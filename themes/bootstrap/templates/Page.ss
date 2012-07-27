<!DOCTYPE html>
<html lang="en">
  <head>
    <% base_tag %>
    <meta charset="utf-8">
    <title>$SiteConfig.Title</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">

    <!-- Le styles -->
    <% require themedCSS(bootstrap) %>
    <style type="text/css">
      body {
        padding-top: 90px;
        padding-bottom: 40px;
      }
    </style>
    <% require themedCSS(bootstrap-responsive) %>

	<% require themedCSS(dashlets) %>
    <!-- Le HTML5 shim, for IE6-8 support of HTML5 elements -->
    <!--[if lt IE 9]>
      <script src="//html5shim.googlecode.com/svn/trunk/html5.js"></script>
    <![endif]-->

    <!-- Le fav and touch icons -->
    <link rel="shortcut icon" href="images/favicon.ico">
    <link rel="apple-touch-icon" href="images/apple-touch-icon.png">
    <link rel="apple-touch-icon" sizes="72x72" href="images/apple-touch-icon-72x72.png">
    <link rel="apple-touch-icon" sizes="114x114" href="images/apple-touch-icon-114x114.png">
  </head>

  <body>
	  
	  <input type="hidden" id="MemberDetails" data-member='$MemberDetails.ATT' />

    <div class="navbar navbar-fixed-top">
      <div class="navbar-inner">
        <div class="container">
      <!-- Uncomment to enable search -->
          <a class="btn btn-navbar" data-toggle="collapse" data-target=".nav-collapse">
            <span class="icon-bar"></span>
          </a>
          <a class="brand" href="">$SiteConfig.Title</a>
	  <% if Menu(1) %>
          <div class="nav-collapse">
            <ul class="nav">
              <li>
			  <% if CurrentMember %>
			  <a href="Security/logout">logout</a>
			  <% else %>
			  <a href="Security/login?BackURL=">login</a>
			  <% end_if %>
			  </li>
            </ul>
          </div><!--/.nav-collapse -->
		  <ul class="nav pull-right">
			  
			  <% control Menu(1) %>
              <li class="$LinkingMode"><a href="$Link" title="Go to $Title">$MenuTitle</a></li>
              <% end_control %>
			  
		  </ul>
          <% end_if %>
        </div>
      </div>
    </div>

    <div class="container">

      $Layout

      <hr>

      <footer>
        <p></p>
      </footer>

    </div> <!-- /container -->

    <!-- Le javascript
    ================================================== -->
    <!-- Placed at the end of the document so the pages load faster -->
    <script src="$ThemeDir/js/jquery.js"></script>
    <script src="$ThemeDir/js/bootstrap-transition.js"></script>
    <script src="$ThemeDir/js/bootstrap-alert.js"></script>
    <script src="$ThemeDir/js/bootstrap-modal.js"></script>
    <script src="$ThemeDir/js/bootstrap-dropdown.js"></script>
    <script src="$ThemeDir/js/bootstrap-scrollspy.js"></script>
    <script src="$ThemeDir/js/bootstrap-tab.js"></script>
    <script src="$ThemeDir/js/bootstrap-tooltip.js"></script>
    <script src="$ThemeDir/js/bootstrap-popover.js"></script>
    <script src="$ThemeDir/js/bootstrap-button.js"></script>
    <script src="$ThemeDir/js/bootstrap-collapse.js"></script>
    <script src="$ThemeDir/js/bootstrap-carousel.js"></script>
    <script src="$ThemeDir/js/bootstrap-typeahead.js"></script>

	<script src="$ThemeDir/js/jquery.timeago.js"></script>
  </body>
</html>

