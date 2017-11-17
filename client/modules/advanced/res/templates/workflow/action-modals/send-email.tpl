<style type="text/css">
    .field-toSpecifiedTeams .list-group {
        margin-bottom: 0px;
    }
</style>
<div class="execution-time-container form-group">{{{executionTime}}}</div>
<div class="row">
    <div class="cell col-sm-6 form-group">
        <label class="control-label">{{translate 'From' scope='Workflow'}}</label>
        <div>
            <select class="form-control" name="from">{{{fromOptions}}}</select>
        </div>
    </div>
    <div class="cell col-sm-6 from-email-container hidden form-group">
        <label class="control-label">{{translate 'Email Address' scope='Workflow'}}</label>
        <div>
            <input class="form-control" name="fromEmail" value="{{fromEmailValue}}">
        </div>
    </div>
</div>
<div class="row">
    <div class="cell col-sm-6 form-group">
        <label class="control-label">{{translate 'To' scope='Workflow'}}</label>
        <div>
            <select class="form-control" name="to">{{{toOptions}}}</select>
        </div>
    </div>
    <div class="cell col-sm-6 to-email-container hidden form-group">
        <label class="control-label">{{translate 'Email Address' scope='Workflow'}}</label>
        <div>
            <input class="form-control" name="toEmail" value="{{toEmailValue}}">
        </div>
    </div>
    <div class="cell col-sm-6 to-teams-container hidden form-group">
        <label class="control-label">{{translate 'Team' category='scopeNamesPlural'}}</label>
        <div class="field-toSpecifiedTeams">
            {{{toSpecifiedTeams}}}
        </div>
    </div>
</div>
<div class="row">
    <div class="cell cell-emailTemplate col-sm-6 form-group">
        <label class="control-label">{{translate 'Email Template' scope='Workflow'}}</label>
        <div class="field field-emailTemplate">{{{emailTemplate}}}</div>
    </div>
</div>
