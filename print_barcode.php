 <style>
	button {
  background-color: #4CAF50; /* Green */
  border: none;
  color: white;
  margin: 10px;
  padding: 15px 32px;
  text-align: center;
  text-decoration: none;
  display: inline-block;
  font-size: 16px;
  tooltip:cursor;
}
 </style>
 <link rel="stylesheet" type="text/css" href="print.min.css">
 <script src="dist/scripts/jquery.min.js"></script>
 <script src="JsBarcode.all.min.js"></script>
<?php
if(isset($_POST)){
	if($_POST['barcode_data']!='' && $_POST['barcode_count']!='' && $_POST['barcode_count']!=0){
		echo "<div id='printTable'>";
		for($i=0;$i<$_POST['barcode_count'];$i++){
		    echo '<svg class="barcode"
  jsbarcode-value="'.$_POST['barcode_data'].'"
  jsbarcode-height="50"
  jsbarcode-textmargin="0"
  jsbarcode-fontoptions="bold">
</svg>';
		//	echo "<img alt='testing' src='barcode/barcode.php?codetype=Code39&size=40&text=".$_POST['barcode_data']."&print=true'/>";
		}
		echo "</div>";?>
		<!-- <button onclick="printJS('printTable', 'html')">Print</button>  -->
<?php	}
}
?>
<script src="print.min.js"></script>
<script>
JsBarcode(".barcode").init();
function printData()
{
   var divToPrint=document.getElementById("printTable");
   newWin= window.open("");
   newWin.document.write(divToPrint.outerHTML);
   newWin.print();
   newWin.close();
}
$(window).on('load', function(){
 // executes when complete page is fully loaded, including all frames, objects and images
 window.print();
});
</script>