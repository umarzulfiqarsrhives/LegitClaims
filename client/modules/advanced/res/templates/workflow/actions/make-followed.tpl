<div class="row">
    {{#unless readOnly}}
        <div class="col-md-1">
            <button class="btn btn-default btn-sm" type="button" data-action='editAction'>{{translate 'Edit'}}</button>
        </div>
    {{/unless}}

    <div class="col-md-10">
        {{translate actionType scope='Workflow' category='actionTypes'}}
        <div class="field-whatToFollow">{{{whatToFollow}}}</div>
        <div class="field-users-to-make-to-follow">{{{usersToMakeToFollow}}}</div>
    </div>
</div>

