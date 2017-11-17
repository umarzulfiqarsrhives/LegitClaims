{{#unless itemDataList.length}}
    {{#ifNotEqual mode 'edit'}}
        {{translate 'None'}}
    {{/ifNotEqual}}
{{/unless}}

{{#if itemDataList.length}}
<table class="table">
<thead>
<tr>
    <th>
        <label>
            {{translate 'name' category='fields' scope='OpportunityItem'}}
        </label>
    </th>
    <th width="10%">
        <label>
            {{translate 'qty' category='fields' scope='OpportunityItem'}}
        </label>
    </th>
    <th width="20%">
        <label>
            {{translate 'unitPrice' category='fields' scope='OpportunityItem'}}
        </label>
    </th>
    <th width="20%">
        <label class="pull-right">
            {{translate 'amount' category='fields' scope='OpportunityItem'}}
        </label>
    </th>
    <th width="50">
        &nbsp;
    </th>
</tr>
</thead>

<tbody class="item-list-internal-container">
{{#each itemDataList}}
    <tr class="item-container-{{id}}" data-id="{{id}}">
    {{{var key ../this}}}
    </tr>
{{/each}}
</tbody>
</table>
{{/if}}