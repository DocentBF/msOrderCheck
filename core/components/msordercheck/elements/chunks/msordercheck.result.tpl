<tr>
    <td>{$num}</td>
    <td>{$createdon|date_format:"%H:%M:%S %d-%m-%Y"}</td>
    <td>{$cost}</td>
    <td>{$comment}</td>
    <td {if $color}style="color:#{$color};"{/if}>{$status}</td>
    <td>{$change_time|date_format:"%H:%M:%S %d-%m-%Y"}</td>
</tr>