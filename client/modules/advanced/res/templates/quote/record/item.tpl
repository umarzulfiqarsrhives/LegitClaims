
<td>
    <div class="field-name">
        {{{name}}}
    </div>
</td>
<td width="9%">
    <div class="field-quantity">
        {{{quantity}}}
    </div>
</td>
<td width="10%">
    <div class="field-taxRate">
        {{{taxRate}}}
    </div>
</td>
<td width="15%">
    <div class="field-listPrice">
        {{{listPrice}}}
    </div>
</td>
<td width="15%">
    <div class="field-unitPrice">
        {{{unitPrice}}}
    </div>
</td>
<td width="15%">
    <div class="field-amount pull-right{{#ifEqual mode 'edit'}} detail-field-container{{/ifEqual}}">
        {{{amount}}}
    </div>
</td>
<td width="50">
    <div class="{{#ifEqual mode 'edit'}} detail-field-container{{/ifEqual}}">
        {{#ifEqual mode 'edit'}}
        <span class="glyphicon glyphicon-magnet drag-icon text-muted" style="cursor: pointer;"></span>
        <a href="javascript:" class="pull-right" data-action="removeItem" data-id="{{id}}" title="{{translate 'Remove'}}"><span class="glyphicon glyphicon-remove"></span></a>
        {{/ifEqual}}
    </div>
</td>

