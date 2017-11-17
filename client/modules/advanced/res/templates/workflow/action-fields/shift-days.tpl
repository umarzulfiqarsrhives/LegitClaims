{{#if readOnly}}
    {{translate shiftDaysOperator scope='Workflow' category='labels'}} {{value}} {{translate 'days' scope='Workflow' category='labels'}}
{{else}}
<div class="row">
    <div class="col-sm-6">
        <select class="form-control" name="shiftDaysOperator">
            <option {{#ifEqual shiftDaysOperator 'plus'}}selected{{/ifEqual}} value="plus">{{translate 'plus' scope='Workflow' category='labels'}}</option>
            <option {{#ifEqual shiftDaysOperator 'minus'}}selected{{/ifEqual}} value="minus">{{translate 'minus' scope='Workflow' category='labels'}}</option>
        </select>
    </div>
    <div class="col-sm-6">
        <div class="input-group">
            <input name="shiftDays" class="form-control" value="{{value}}">
            <span class="small input-group-addon">{{translate 'days' scope='Workflow' category='labels'}}</span>
        </div>
    </div>
</div>
{{/if}}