{if ($fields is iterable) && (!empty($fields))}
    <form action="#" method="post" class="msOrderCheck">
        {foreach $fields as $fieldname}
            <div class="form-group">
                <label for="oc_{$fieldname}">{('msordercheck_frontend_' ~ $fieldname)|lexicon}</label>
                <input type="text" class="form-control" placeholder="" id="oc_{$fieldname}" name="{$fieldname}">
            </div>
        {/foreach}
        <button type="submit" class="btn btn-primary">{'msordercheck_frontend_submit'|lexicon}</button>
    </form>
    <div class="msOrderCheckResult"></div>
{else}
    {'msordercheck_err_fields_ns'|lexicon}
{/if}