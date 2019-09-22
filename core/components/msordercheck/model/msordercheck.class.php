<?php

class msOrderCheck
{
    /** @var modX $modx */
    public $modx, $condition;
    protected $query;


    /**
     * @param modX $modx
     * @param array $config
     */
    function __construct(modX &$modx, array $config = [])
    {
        $this->modx =& $modx;
        $corePath = $this->modx->getOption('msordercheck_core_path', null,
            $this->modx->getOption('core_path') . 'components/msordercheck/'
        );
        $assetsUrl = $this->modx->getOption('msordercheck_assets_url', null,
            $this->modx->getOption('assets_url') . 'components/msordercheck/'
        );

        $fields = trim($this->modx->getOption('fields', $config, 'phone,num'));

        $this->config = array_merge([
            'corePath' => $corePath,
            'modelPath' => $corePath . 'model/',
            'processorsPath' => $corePath . 'processors/',

            'actionUrl' => $assetsUrl . 'action.php',
            'assetsUrl' => $assetsUrl,

            'frontend_css' => '[[+assetsUrl]]css/default.css',
            'frontend_js' => '[[+assetsUrl]]js/default.js',

            'form' => '.msOrderCheck',
            'resultBlock' => '.msOrderCheckResult',

            'fields' => $fields,
            'allRequired' => true,
            'showHistory' => true,
            'historySortBy' => 'createdon',
            'historySortDir' => 'ASC',
        ], $config);

        $this->modx->addPackage('msordercheck', $this->config['modelPath']);
        $this->modx->lexicon->load('msordercheck:default');
    }


    public function process($request)
    {
        if (!isset($_SESSION['msOrderCheck']['settings'][$request['msoch_token']])) {
            return $this->error('msordercheck_err_token_nf');
        }

        $config = $_SESSION['msOrderCheck']['settings'][$request['msoch_token']];
        if (empty($config['fields'])) {
            return $this->error('msordercheck_err_fields_ns');
        }

        $data = $this->query($config, $request);
        if ($data['status']) {
            $rowsCount = count($data['data']);
            $output = '';
            if ($rowsCount > 0) {
                $tplResult = $this->modx->getOption('tplResult', $config, 'tpl.msOrderCheck.result');
                $tplWrapper = $this->modx->getOption('tplWrapper', $config, 'tpl.msOrderCheck.result.wrapper');
                foreach ($data['data'] as $row) {
                    $output .= $this->getChunk($tplResult, $row);
                }
                if (empty($output)) {
                    $output = '<pre>' . print_r($data['data'], 1) . '</pre>';
                } elseif(!empty($tplWrapper)) {
                    $output = $this->getChunk($tplWrapper, array('output' => $output));
                }
            }

            return $this->success($rowsCount > 0 ? 'success' : 'msordercheck_err_result_nf', array(
                'total' => $rowsCount,
                'rows' => $output
            ));
        } else {
            return $this->error($data['message']);
        }
    }

    /**
     * @param $config
     * @param array $formData
     * @return array
     */
    protected function query($config, $formData = array())
    {
        $response = array(
            'status' => false,
            'message' => '',
            'data' => array()
        );
        $addSelect = '';
        $fields = $config['fields'];
        $this->query = $this->modx->newQuery('msOrder');

        $this->query->leftJoin('msOrderAddress', 'Address');
        $this->query->leftJoin('msDelivery', 'Delivery');
        $this->query->leftJoin('msPayment', 'Payment');

        $this->query->sortby('createdon','ASC');

        if ($config['showHistory']) {
            $this->query->leftJoin('msOrderLog', 'Log');
            $this->query->leftJoin('msOrderStatus', 'Status', '`Log`.`entry` = `Status`.`id`');
            $addSelect = ", `Log`.`timestamp` as `change_time`";
            $this->query->sortby($config['historySortBy'], $config['historySortDir']);
        } else {
            $this->query->leftJoin('msOrderStatus', 'Status');
        }

        $this->query->select(
            $this->modx->getSelectColumns('msOrder', 'msOrder', '', array('status', 'delivery', 'payment', 'address', 'updatedon'), true) . ',
        Status.name as status, Status.color, Delivery.name as delivery, Payment.name as payment,' .
            $this->modx->getSelectColumns('msOrderAddress', 'Address', '', array('id', 'user_id', 'createdon', 'updatedon', 'properties'), true) . $addSelect
        );

        if (!$this->setQueryWhere($fields, $formData, $config['allRequired'])) {
            $response['message'] = 'msordercheck_err_build_where';
            return $response;
        }

        $this->query->prepare();
        if ($this->query->stmt->execute()) {
            $data = $this->query->stmt->fetchAll(PDO::FETCH_ASSOC);
            $response['status'] = true;
            $response['data'] = $data;
        }

        return $response;
    }

    /**
     * @param array $fields
     * @param array $formData
     * @param bool $condition
     * @return bool
     */
    protected function setQueryWhere($fields = array(), $formData = array(), $condition = true)
    {
        if (empty($fields) || empty($formData)) return false;
        $condition = $condition ? xPDOQuery::SQL_AND : xPDOQuery::SQL_OR;
        $criteriaCount = 0;
        foreach ($fields as $field) {
            if (!isset($formData[$field]) || empty($formData[$field]))
                continue;

            $fieldValue = $this->modx->quote(trim($formData[$field]));
            $this->query->where('`'.$field . '`=' . $fieldValue, $condition);
            $criteriaCount++;
        }

        if(!$criteriaCount)
            $this->query->where("1!=1", xPDOQuery::SQL_AND);

        return true;
    }

    /**
     * @param string $message
     * @param array $data
     * @param array $placeholders
     * @return mixed
     */
    public function error($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => false,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        );
        return $this->modx->toJSON($response);
    }

    /**
     * This method returns an success of request
     *
     * @param string $message A lexicon key for success message
     * @param array $data .Additional data, for example cart status
     * @param array $placeholders Array with placeholders for lexicon entry
     *
     * @return array|string $response
     */
    public function success($message = '', $data = array(), $placeholders = array())
    {
        $response = array(
            'success' => true,
            'message' => $this->modx->lexicon($message, $placeholders),
            'data' => $data,
        );
        return $this->modx->toJSON($response);
    }

    /**
     *  js & css load
     */
    public function loadAssets()
    {
        if ($css = trim($this->config['frontend_css'])) {
            if (preg_match('/\.css/i', $css)) {
                $this->modx->regClientCSS(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $css));
            }
        }
        if ($js = trim($this->config['frontend_js'])) {
            if (preg_match('/\.js/i', $js)) {
                $this->modx->regClientScript(str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $js));
            }
        }
        $config = $this->modx->toJSON(array(
            'actionUrl' => str_replace('[[+assetsUrl]]', $this->config['assetsUrl'], $this->config['actionUrl']),
            'form' => $this->config['form'],
            'resultBlock' => $this->config['resultBlock'],
        ));
        $this->modx->regClientScript(
            "<script>msOrderCheck.init({$config});</script>", true
        );
    }

    /**
     * @param $chunk
     * @param $properties
     * @return mixed
     */
    public function getChunk($chunk, $properties)
    {
        $pdo = $this->modx->getService('pdoTools');
        return $pdo->getChunk($chunk, $properties);
    }

    /**
     * Проверка существующих полей таблиц
     * @return array
     */
    public function getValidFields()
    {
        $fields = array();
        $validnames = array();

        $classes = array('msOrder', 'msOrderAddress', 'msOrderStatus', 'msDelivery', 'msPayment');
        foreach ($classes as $cls) {
            $validnames = array_merge($validnames, array_keys($this->modx->getFields($cls)));
        }

        $this->config['fields'] = explode(",", $this->config['fields']);
        foreach ($this->config['fields'] as $fieldname) {
            $fieldname = trim($fieldname);
            if (in_array($fieldname, $validnames)) {
                $fields[] = $fieldname;
            } else {
                $this->modx->log(modX::LOG_LEVEL_ERROR, $this->modx->lexicon('msordercheck_err_field_ne', array('field' => $fieldname)));
            }
        }
        return $fields;
    }
}