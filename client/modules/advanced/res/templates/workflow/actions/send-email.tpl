<div class="row">
    {{#unless readOnly}}
        <div class="col-md-1">
            <button class="btn btn-default btn-sm" type="button" data-action='editAction'>{{translate 'Edit'}}</button>
        </div>
    {{/unless}}

    <div class="col-md-10">
        {{translate actionType scope='Workflow' category='actionTypes'}}

        <div class="field-list small" style="margin-top: 12px;">
            <div class="field-row cell form-group execution-time-container" data-field="execution-time">
                <div class="field" data-field="execution-time">{{{executionTime}}}</div>
            </div>

            {{#if actionData.from}}
                <div class="field-row cell form-group" data-field="from">
                    <label class="control-label">{{translate 'From' scope='Workflow'}}</label>
                    <div class="field-container field" data-field="from">
                        {{#ifEqual actionData.from 'specifiedEmailAddress'}}
                            {{actionData.fromEmail}}
                        {{else}}
                            {{actionData.fromLabel}}
                        {{/ifEqual}}
                    </div>
                </div>
            {{/if}}

            {{#if actionData.to}}
                <div class="field-row cell form-group" data-field="to">
                    <label class="control-label">{{translate 'To' scope='Workflow'}}</label>
                    <div class="field-container field" data-field="to">
                        {{#ifEqual actionData.to 'specifiedEmailAddress'}}
                            {{actionData.toEmail}}
                        {{else}}
                            {{actionData.toLabel}}
                        {{/ifEqual}}
                        {{#ifEqual actionData.to 'specifiedTeams'}}
                            <div class="field-container field field-toSpecifiedTeams" data-field="toSpecifiedTeams">{{{toSpecifiedTeams}}}</div>
                        {{/ifEqual}}
                    </div>
                </div>
            {{/if}}

            {{#if actionData.emailTemplateId}}
                <div class="field-row cell form-group" data-field="emailTemplate">
                    <label class="control-label">{{translate 'Email Template' scope='Workflow' category='labels'}}</label>
                    <div class="field-container field" data-field="emailTemplate">{{{emailTemplate}}}</div>
                </div>
            {{/if}}
        </div>
    </div>
</div>