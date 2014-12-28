<header >
  <div class="headerstrip " >
    <div class="container">
      <div class="pull-left welcometxt"> Welcome to Simplepx, <a class="orange" href="login.html">Login</a> or <a class="orange" href="register.html">Create new account</a> </div>
      <!-- Top Nav Start -->
      <div class="pull-right">
        <div class="navbar" id="topnav">
          <div class="navbar-inner">
            <ul class="nav" >
              <li><a class="home active" ><i class="icon-home"></i> Home </a> </li>
              <li><a class="myaccount" ><i class="icon-user"></i> My Account </a> </li>
              <li><a class="shoppingcart" ><i class="icon-shopping-cart"></i> Shopping Cart </a> </li>
              <li><a class="checkout" ><i class="icon-ok-circle"></i> CheckOut </a> </li>
            </ul>
          </div>
        </div>
      </div>
      <!-- Top Nav End -->
      
      <div class="pull-right">
        <ul class="nav language pull-left">
          <li class="dropdown hover"> <a  class="dropdown-toggle" data-toggle="">US DOLLAR <b class="caret"></b></a>
            <ul class="dropdown-menu currency">
              <li><a >US DOLLAR</a> </li>
              <li><a >Euro </a> </li>
              <li><a >British Pound</a> </li>
            </ul>
          </li>
          <li class="dropdown hover"> <a  class="dropdown-toggle" data-toggle="">English <b class="caret"></b></a>
            <ul class="dropdown-menu language">
              <li><a >English</a> </li>
              <li><a >Spanish</a> </li>
              <li><a >German</a> </li>
            </ul>
          </li>
        </ul>
      </div>
    </div>
  </div>
  <div class="headerdetails " >
    <div class="container"> <a class="logo pull-left logo-size" href="index-2.html"><img title="Simplepx" alt="Simplepx" src="<?php echo base_url();?>assets/front/img/logo.png"></a> 
      <!--Social -->
      <div class="socialtop pull-right">
        <div class="call"><i class="icon-phone"></i> Call Us : <span class="orange">+ 00 123 456 7890 </span></div>
        <ul class="pull-right">
          <li><a class="tooltip-test"  data-original-title="Facebook" href="https://www.facebook.com/techzmarket.net" ><i class="icon-facebook"></i></a></li>
          <li><a class="tooltip-test"  data-original-title="Twitter"  ><i class="icon-twitter"></i></a></li>          
          <li><a class="tooltip-test"  data-original-title="Google Plus"  ><i class="icon-google-plus"></i></a></li>          
          <li><a class="tooltip-test"  data-original-title="Flickr"  ><i class="icon-flickr"></i></a></li>
          <li><a class="tooltip-test"  data-original-title="Skype"  ><i class="icon-skype"></i></a></li>
        </ul>
      </div>
      <div>
        <ul class="nav topcart pull-right">
          <li class="dropdown hover carticon "> <a  class="dropdown-toggle" > <i class="icon-shopping-cart font18"></i> Giỏ hàng <span class="label label-orange font14">100 sp</span> - 3 triệu 2 <b class="caret"></b></a>
            <ul class="dropdown-menu topcartopen ">
              <li>
				<!--
					<table>
					<tr>
						<td>Không có sản phẩm nào</td>
					</tr>
					</table>
				-->
				<table>
                  <tbody>
                    <tr>
                      
                      <td class="name"><a href="product.html">MacBook</a></td>
                      <td class="quantity">x1</td>
                      <td class="total">200 ngàn <a href="javascript:;"><i class="icon-remove "></i></a></td>
                      
                    </tr>
                    <tr>
                      
                      <td class="name"><a href="product.html">MacBook</a></td>
                      <td class="quantity">x1</td>
                      <td class="total">3 triệu <a href="javascript:;"><i class="icon-remove "></i></a></td>
                      
                    </tr>
                  </tbody>
                </table>
                <table>
                  <tbody>
                    <tr>
                      <td class="textright"><b>Sub-Total:</b></td>
                      <td class="textright">$500.00</td>
                    </tr>
                    <tr>
                      <td class="textright"><b>Eco Tax (-2.00):</b></td>
                      <td class="textright">$2.00</td>
                    </tr>
                    <tr>
                      <td class="textright"><b>VAT (17.5%):</b></td>
                      <td class="textright">$87.50</td>
                    </tr>
                    <tr>
                      <td class="textright"><b>Total:</b></td>
                      <td class="textright">$589.50</td>
                    </tr>
                  </tbody>
                </table>
                <div class="well pull-right buttonwrap"> <a class="btn btn-orange" >View Cart</a> <a class="btn btn-orange" >Checkout</a> </div>
              </li>
            </ul>
          </li>
        </ul>
      </div>
      <!--Top Search -->
	  
	<div class="pull-right topsearch">
        <form class="form-inline">
		  <select class="form-control">
			<option>Chon</option>
		  </select>
          <input type="search" placeholder="Search Here" class="form-control">
          <button data-original-title="Search" class="btn btn-orange btn-small tooltip-test form-control"> <i class="icon-search icon-white"></i> </button>
        </form>
      </div>
	  
    </div>
  </div>
  <div id="mainmenu"  class="mb40" > 
    <!-- Navigation -->
    <nav class="subnav ">
      <div class="container menurelative" >        
        <ul class="nav-pills mainmenucontain container">
        <li><a href="<?php echo base_url();?>"><i class="icon-home icon-white font18"></i></a></li>
        <?php echo $menu_top;?>        
        </ul>
        <a  class="quickcontact"><i class="icon-phone"></i> Buy Now</a> </div>
    </nav>
  </div>
</header>