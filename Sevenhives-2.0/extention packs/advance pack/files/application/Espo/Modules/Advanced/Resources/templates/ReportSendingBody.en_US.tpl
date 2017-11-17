<p>{reportDescription}</p>
<p><b>{reportHeader}</b></p>
<table cellspacing="0" cellpadding="5" border="1">
    <tr>
    {loop columnNames}
       <th><b>{value}</b></th>
    {/loop columnNames}
    </tr>
    {loop resultRows}
        <tr>
        {loop resultCols}
           <td>{wrapperStart}{value}{wrapperEnd}</td>
        {/loop resultCols}
        </tr>
    {/loop resultRows}
</table>
