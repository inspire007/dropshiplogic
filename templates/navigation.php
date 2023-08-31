<!-- BEGIN BODY -->
<body class="fixed-top">
	 <script type="text/javascript">
		var curtain = document.body.appendChild(document.createElement('div'));
		curtain.id = "curtain";
		curtain.onkeypress = curtain.onclick = function(){ return false; }
	</script>
	<!-- BEGIN HEADER -->
	<div class="header navbar navbar-inverse navbar-fixed-top">
		<!-- BEGIN TOP NAVIGATION BAR -->
		<div class="navbar-inner">
			<div class="container-fluid">
				<!-- BEGIN LOGO -->
				<a class="brand" href="index.php">
				<?php echo PAGE_TITLE?>
				</a>
				<!-- END LOGO -->
				<!-- BEGIN RESPONSIVE MENU TOGGLER -->
				<a href="javascript:void(0);" class="btn-navbar collapsed" data-toggle="collapse" data-target=".nav-collapse">
				<img src="assets/img/menu-toggler.png" alt="" />
				</a>          
				<!-- END RESPONSIVE MENU TOGGLER -->				
				<!-- BEGIN TOP NAVIGATION MENU -->					
				<ul class="nav pull-right">
					<!-- BEGIN USER LOGIN DROPDOWN -->
                    <?php if(!empty($_SESSION['user_logged_in'])){?>
					<li class="dropdown user">
						<a href="javascript:void(0)" class="dropdown-toggle" data-toggle="dropdown">
						<img alt="" src="assets/img/avatar_1.png" />
						<span class="username">&nbsp;&nbsp;<?php echo $_SESSION['user_logged_in']?></span>
						<i class="icon-angle-down"></i>
						</a>
						<ul class="dropdown-menu">
							<li><a href="logout.php"><i class="icon-key"></i> Log Out</a></li>
						</ul>
					</li>
                    <?php }else{?>
                    <li class="dropdown user">
						<a href="login.php">
						<span class="username">Login</span>
						<i class="icon-angle-down"></i>
						</a>
					</li>
                    <?php }?>
					<!-- END USER LOGIN DROPDOWN -->
				</ul>
				<!-- END TOP NAVIGATION MENU -->	
                <?php if(preg_match('/index\.php/', $_SERVER['SCRIPT_FILENAME'])){?>
                <div class="pull-right" style="margin-top:10px">
                	<i class="icon icon-arrow-up upArrow" style="cursor: pointer" title="Scroll up"></i>
                	&nbsp;
                    <i class="icon icon-arrow-down downArrow" style="cursor: pointer" title="Scroll down"></i>
                	&nbsp;
                	<i class="icon icon-arrow-left leftArrow" style="cursor: pointer" title="Scroll table to left"></i>
                	&nbsp;
                    <i class="icon icon-arrow-right rightArrow" style="cursor: pointer" title="Scroll table to right"></i>
                    &nbsp;
                </div>
                <?php }?>
			</div>
		</div>
		<!-- END TOP NAVIGATION BAR -->
	</div>
	<!-- END HEADER -->
	<!-- BEGIN CONTAINER -->
	<div class="page-container sidebar-closed row-fluid">
		<!-- BEGIN SIDEBAR -->
		<div class="page-sidebar nav-collapse collapse">
			<!-- BEGIN SIDEBAR MENU -->        	
			<ul>
				<li>
					<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
					<div class="sidebar-toggler hidden-phone"></div>
					<!-- BEGIN SIDEBAR TOGGLER BUTTON -->
				</li>
				<li class="start nav-index" style="margin-top: 10px">
					<a href="index.php">
					<i class="icon-home"></i> 
					<span class="title">Dashboard</span>
					<span class="selected"></span>
					</a>
				</li>
				<li class="has-sub nav-conf">
					<a href="javascript:void(0)">
					<i class="icon-cog"></i> 
					<span class="title">Configurations</span>
                    <span class="arrow "></span>
					</a>
                    <ul class="sub">
						<li ><a href="settings.php">Settings</a></li>
						<li ><a href="add_account.php">Add Ebay Account</a></li>
                        <li ><a href="add_account.php?amazon=1">Add Amazon Account</a></li>
					</ul>
				</li>
                <li class="nav-stats">
					<a href="stats.php">
					<i class="icon-bar-chart"></i> 
					<span class="title">Sales Statistics</span>
					</a>
				</li>
                <li class="nav-listings">
					<a href="listings.php">
					<i class="icon-th-list"></i> 
					<span class="title">Listed Items</span>
					</a>
				</li>
                
                <!--confidential links-->
                <?php if(!empty($_SESSION['user_logged_in'])){?>
                <li class="nav-ebayimport has-sub">
                    <a href="javascript:void(0)">
						<i class="icon-refresh"></i> 
						<span class="title">Import Orders</span>
                    	<span class="arrow "></span>
					</a>
                    
                    <ul class="sub">
                   	 	<li>
                            <a href="cron/listingsync-cron.php" 
                                onclick="if(!confirm('This will import all items from Ebay and Amazon and done automatically at an interval. '
                                     + 'Do you want to manually run this script?'))return false;">
                             
                            	Get listings
                            </a>
                    	</li>
                        <li>
                            <a href="cron/ebay-cron.php" 
                                onclick="if(!confirm('This will import all new orders from eBay and done automatically at an interval. '
                                 + 'Do you want to manually run this script?'))return false;">
                                Get orders from eBay
                            </a>
                    	</li>
                    	<li>
                            <a href="cron/amazon-cron.php" 
                                onclick="if(!confirm('This will import all new orders from Amazon and done automatically at an interval. '
                                     + 'Do you want to manually run this script?'))return false;">
                             
                            	Get orders from Amazon
                            </a>
                    	</li>
                    </ul>
				</li>
                
                <li class="nav-samsorder">
					<a href="cron/sams-cron.php"
                    	onclick="if(!confirm('This will process all queued orders on Samsclub and done automatically at an interval. '
                        	 + 'Do you want to manually run this script? USE THIS WITH CAUTION IN TEST MODE.'))return false;">
					<i class="icon-shopping-cart"></i> 
					<span class="title">Place queued orders</span>
					</a>
				</li>
                
                 <li class="nav-postorder has-sub">
					
                    <a href="javascript:void(0)">
						<i class="icon-barcode"></i> 
						<span class="title">Update sales</span>
                    	<span class="arrow "></span>
					</a>
                    
                    <ul class="sub">
                    	<li>
                            <a href="cron/ebay-postorder.php"
                                onclick="if(!confirm('This will update order numbers and tracking numbers on eBay and done automatically at an interval. '
                                     + 'Do you want to manually run this script?'))return false;">
                            	Update sales records to Ebay
                            </a>
						</li>
                        <li>                    
                            <a href="cron/amazon-postorder.php"
                                onclick="if(!confirm('This will update order numbers and tracking numbers on Amazon and done automatically at an interval. '
                                     + 'Do you want to manually run this script?'))return false;">
                               	Update sales records to Amazon
                            </a>
                         </li>
                    </ul>
				</li>
                
                <li class="nav-inv">
					<a href="cron/inventory-cron.php"
                    	onclick="if(!confirm('This will check listings for stock in SamsClub and done automatically at an interval. '
                        		+ 'Do you want to manually run this script?'))return false;">
					<i class="icon-play-circle"></i> 
					<span class="title">Check Sams Stock</span>
					</a>
				</li>
                
                <li class="nav-postorder">
					<a href="cron/osc-cron.php"
                    	onclick="if(!confirm('This will add OutOfStockControl flag to items listed today.Continue?'))return false;">
					<i class="icon-adjust"></i> 
					<span class="title">Add OutOfStockControl</span>
					</a>
				</li>
                <?php }?>
                <!--confidential links-->
			</ul>
			<!-- END SIDEBAR MENU -->
		</div>
		<!-- END SIDEBAR -->
		<!-- BEGIN PAGE -->
		<div class="page-content">
