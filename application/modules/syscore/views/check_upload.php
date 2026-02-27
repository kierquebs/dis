<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<title>COREPASS UPDATE FUNCTIONS</title>

	<style type="text/css">

	::selection { background-color: #E13300; color: white; }
	::-moz-selection { background-color: #E13300; color: white; }

	body {
		background-color: #fff;
		margin: 40px;
		font: 13px/20px normal Helvetica, Arial, sans-serif;
		color: #4F5155;
	}

	a {
		color: #003399;
		background-color: transparent;
		font-weight: normal;
	}

	h1 {
		color: #444;
		background-color: transparent;
		border-bottom: 1px solid #D0D0D0;
		font-size: 19px;
		font-weight: normal;
		margin: 0 0 14px 0;
		padding: 14px 15px 10px 15px;
	}

	code {
		font-family: Consolas, Monaco, Courier New, Courier, monospace;
		font-size: 12px;
		background-color: #f9f9f9;
		border: 1px solid #D0D0D0;
		color: #002166;
		display: block;
		margin: 14px 0 14px 0;
		padding: 12px 10px 12px 10px;
	}

	p.footer {
		text-align: right;
		font-size: 11px;
		border-top: 1px solid #D0D0D0;
		line-height: 32px;
		padding: 0 10px 0 10px;
		margin: 20px 0 0 0;
	}

	#container {
		margin: 10px;
		border: 1px solid #D0D0D0;
		box-shadow: 0 0 8px #D0D0D0;
	}   
        #body {
            margin: 0 auto 15px auto;
            width: 95%;
        }
        ._l {
            width: 48%;
            display:inline-block;
            vertical-align: top;
        }
        ._r {
            width: 48%;
            display:inline-block;
            vertical-align: top;
        }
		table{
			font-size: 11px !important;
		}
	</style>
</head>
<body>

<div id="container">
	<h1>AUDIT UPLOAD FILES</h1>
	<div id="body">
        <div class="_l">
            <table border='1' width='98%'>
                <thead>
                   <tr><th colspan="4">REDEEM</th> </tr>
                   <tr><th>FILEDATE</th><th>FILENAME</th><th>ID</th><th width='150px'>DATE</th></tr>
                </thead> 
                <tbody>                   
                <?php
                if($redemption->num_rows() <> 0):
                    foreach($redemption->result() as $row1):
                ?>
                        <tr><td><?php echo $row1->file_date;?></td><td><?php echo $row1->file_name;?></td><td><?php echo $row1->id;?></td><td><?php echo $row1->date_created;?></td></tr>                   
                <?php endforeach; 
                else: ?>
                    <tr><td colspan="4"><center><i>No Result Found!</i></center></tr>  
                <?php endif;?>
                </tbody>
            </table>
        </div>
        <div class="_r">
            <table border='1' width='98%'>
                <thead>
                   <tr><th colspan="4">RECON</th> </tr>
                   <tr><th>FILEDATE</th><th>FILENAME</th><th>ID</th><th width='150px'>DATE</th></tr>
                </thead> 
                <tbody>                   
                <?php
                if($reconciliation->num_rows() <> 0):
                    foreach($reconciliation->result() as $row2):
                ?>
                   <tr><td><?php echo $row2->file_date;?></td><td><?php echo $row2->file_name;?></td><td><?php echo $row2->id;?></td><td><?php echo $row2->date_created;?></td></tr>                   
                <?php endforeach; 
                else: ?>
                    <tr><td colspan="4"><center><i>No Result Found!</i></center></tr>  
                <?php endif;?>
                </tbody>
            </table>
        </div>
	</div>	
	
</div>

</body>
</html>
