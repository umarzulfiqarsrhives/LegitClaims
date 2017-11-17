<div class="button-container">
	<button class="btn btn-primary" data-action="save">{{translate 'Save'}}</button>
	<button class="btn btn-default" data-action="cancel">{{translate 'Cancel'}}</button>
</div>

<div class="row">
	<div class="col-sm-6">	
		<div>
			<div class="cell cell-enabled form-group">
				<label class="control-label">{{translate 'enabled' scope='Integration' category='fields'}}</label>
				<div class="field field-enabled">{{{enabled}}}</div>
			</div>
		</div>
		<div class="data-panel">
		    <div class="cell cell-connected form-group">
			    <button type="button" class="btn btn-danger {{#if isConnected}}hidden{{/if}}" data-action="connect">{{translate 'Connect' scope='ExternalAccount'}}</button>
			    <span class="connected-label label label-success {{#unless isConnected}}hidden{{/unless}}">{{translate 'Connected' scope='ExternalAccount'}}</span>
			</div>
            <div class="data-panel-connected" >
			    {{#each fields}}
			        {{#ifNotEqual ./this 'enabled'}} 
			        
			        <div class="cell cell-{{./this}} form-group">
                        <label class="control-label">{{translate ./this scope='ExternalAccount' category='fields'}}</label>
                        <div class="field field-{{./this}}"> {{var this ../../this}} </div>
                    </div> 
                    {{/ifNotEqual}}
                       
			        
                {{/each}}
            
			</div>
		</div>
	</div>
	<div class="col-sm-6">
		{{#if helpText}}
		<div class="well">			
			{{{../helpText}}}			
		</div>
		{{/if}}
	</div>
</div>


