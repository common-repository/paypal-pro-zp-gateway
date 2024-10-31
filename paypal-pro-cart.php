<?php
global $dl_paypalpro,$pp_opts,$pp_errors;
global $pp_vars;
global $zp_vars;
?>
<table id="payPalPro">
	<tr>
	  <td colspan='2'><table id='payPalProInner'>
          <tr>
            <td colspan="2"  class="productbox">Your Selected Items: </td>
          </tr>
          <tr>
            <td><strong>Item Name </strong></td>
            <td><strong>Price</strong></td>
          </tr>
          <tr>
            <td><?php echo $zp_vars['item_name'];?></td>
            <td><?php echo currency($zp_vars['amount']);?></td>
          </tr>
          <tr>
            <td><div align="right"><strong>Total</strong></div></td>
            <td><?php echo currency($zp_vars['amount']);?></td>
          </tr>
        </table></td>
    </tr>
</table>