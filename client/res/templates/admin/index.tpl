<div class="page-header"><h3>{{translate 'Administration' scope='Admin'}}</h3></div>

<div class="admin-content">
	<div class="row">
		<div class="col-md-7">
			{{#each links}}
			<h4>{{translate label scope='Admin'}}</h4>
			<table class="table table-bordered">
			    {{#each items}}
			    <tr>
			        <td width="200">
			            <a href="{{url}}">{{translate label scope='Admin' category='labels'}}</a>
			        </td>
			        <td>{{translate description scope='Admin' category='descriptions'}}</td>
			    </tr>
			    {{/each}}
			</table>
			{{/each}}
		</div>
	</div>
</div>