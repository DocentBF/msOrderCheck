<?php
require_once MODX_CORE_PATH . 'model/modx/transport/modpackagebuilder.class.php';
require_once MODX_CORE_PATH . 'model/modx/rest/modrestcurlclient.class.php';

class encPackageBuilder extends modPackageBuilder
{
    /**
     * encPackageBuilder constructor.
     * @param modX $modx
     * @param $config
     */
    function __construct(modX $modx, $config)
    {
        parent::__construct($modx);

        $keyRequester = new KeyRequester($modx, array(
            KeyRequester::PARAM_API_KEY => $config['store_api_key'], // ключ из modstore.pro
            KeyRequester::PARAM_USERNAME => $config['store_username'], // email аккаунта на modstore.pro
            KeyRequester::PARAM_PACKAGE => $config['name_lower'], // имя пакета
            KeyRequester::PARAM_VERSION => $config['version'] . '-' . $this->config['release'] // версия пакета
        ));
        define('PKG_ENCODE_KEY', $keyRequester->getKey());
    }

    /**
     * @param string $name
     * @param string $version
     * @param string $release
     * @return xPDOTransport
     */
    public function createPackage($name, $version, $release = '')
    {
        parent::createPackage($name, $version, $release);
        $this->package->put(new xPDOFileVehicle, [
            'vehicle_class' => 'xPDOFileVehicle',
            'object' => [
                'source' => dirname(__FILE__) . '/encryptedvehicle.class.php',
                //'target' => "return MODX_CORE_PATH . 'components/" . PKG_NAME_LOWER . "/';"
                'target' => "return MODX_CORE_PATH . 'components/modstoreencrypt/';"
            ]
        ]);

        $this->package->put(new xPDOScriptVehicle, [
            'vehicle_class' => 'xPDOScriptVehicle',
            'object' => [
                'source' => dirname(dirname(__FILE__)) . '/resolvers/resolve.encryption.php'
            ]
        ]);
        return $this->package;
    }

    /**
     * Puts the vehicle into the package.
     *
     * @access public
     * @param modTransportVehicle $vehicle The vehicle to insert into the package.
     * @return boolean True if successful.
     */
    public function putVehicle($vehicle)
    {
        $attr = $vehicle->compile();
        $obj = $vehicle->fetch();
        $attr = array_merge($attr, array(
            'vehicle_class' => EncryptedVehicle::class,
        ));
        return $this->package->put($obj, array_merge($attr));
    }

    public function pack()
    {
        $this->package->put(new xPDOScriptVehicle, [
            'vehicle_class' => 'xPDOScriptVehicle',
            'object' => [
                'source' => dirname(dirname(__FILE__)) . '/resolvers/resolve.encryption.php'
            ]
        ]);
        return parent::pack();
    }
}

class KeyRequester
{
    /** @var string Ключ API, выдаваемый магазином после оплаты. Для автора - генерируется бесплатно. */
    const PARAM_API_KEY = 'api_key';
    /** @var string Имя пользователя для доступа к магазину. В случае modstore.pro - email пользователя. */
    const PARAM_USERNAME = 'username';
    /** @var string Хост сайта. В случае реального MODX - выдается домен, но можно заменить на mysite.dev или mysite.docker. */
    const PARAM_HTTP_HOST = 'http_host';
    /** @var string Имя пакета (без версии) */
    const PARAM_PACKAGE = 'package';
    /** @var string Версия пакета вида 1.0.0-pl */
    const PARAM_VERSION = 'version';
    /** @var string Версия протокола взаимодействия с репозиторием. Обычно 1.1, но это новые специальные запросы, поэтому 2.0.0 */
    const PARAM_VEHICLE_VERSION = 'vehicle_version';
    private $modx;
    /** @var array Массив с параметрами для доступа к репозиторию */
    private $params = array();

    /**
     * keyrequester.class constructor.
     * @param $modx
     * @param array $params
     */
    public function __construct(& $modx, $params = array())
    {
        $this->modx = &$modx;
        $this->params = [
            'api_key' => '',
            'username' => '',
            'http_host' => $_SERVER['HTTP_HOST'],
            'package' => '',
            'version' => '',
            'vehicle_version' => '2.0.0'
        ];
        // Переопределяем параметры теми, которые пришли из кода, которые использует этот класс.
        $this->params = array_merge($this->params, $params);
    }

    /**
     * @return string
     */
    public function getKey()
    {
        // Отправляем запрос к репозиторию и конвертируем ответный XML в массив для удобной работы
        $answer = modRestArrayToXML::toArray($this->request());
        // Если есть сообщение, значит это сообщение об ошибке.
        // Выводим сообщение и прекращаем работу скрипта сборки (так как без пароля она бесмысслена)
        if (isset($answer['message'])) {
            $this->modx->log(modX::LOG_LEVEL_ERROR, $answer['message']);
            echo $answer['message'];
            exit;
        }
        // Если сообщений нет, возвращаем ключ, т.е. пароль, который сгенерировал и выдал репозиторий.
        return $answer['key'];
    }

    /**
     * @return string
     */
    protected function request()
    {
        // Стандартный запрос из кода к удаленному сервису через библиотеку cURL.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, 'https://modstore.pro/extras/package/encode');
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/xml']);
        curl_setopt($ch, CURLOPT_POSTFIELDS, modRestArrayToXML::toXML($this->params, 'request'));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        $result = trim(curl_exec($ch));
        curl_close($ch);
        return $result;
    }
}