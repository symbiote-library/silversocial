<!doctype html>  

<html lang="en">
	<head>
		<% base_tag %>
		
		<meta charset="utf-8">
		<meta name="viewport" content="initial-scale=1.0, width=device-width, maximum-scale=1.0"/>
		
		$MetaTags
	
		<% require themedCSS(layout) %>
		<% require themedCSS(typography) %>
		<% require themedCSS(form) %>
	</head>

	<body>
		<div id="header">
			<div class="container">
				<a id="logo" href="">$SiteConfig.Title</a>
				<div class="loginBox">
					<% if CurrentMember %>
					<a href="Security/logout">Logout $CurrentMember.Title</a>
					<% else %>
						<% control LoginForm %>
						<form $FormAttributes>
						<% control FieldMap %>
						<p>$Email</p>
						<p>$Password</p>
						<input type="submit" value="Login" />
						<% end_control %>
						</form>
						<% end_control %>
					<% end_if %>
				</div>
				<ul id="navigation">
					<% control Menu(1) %>
						<li class="$LinkingMode"><a href="$Link">$MenuTitle.XML</a></li>
					<% end_control %>
				</ul>
			</div>
		</div>
		
		<div id="layout">
			<div class="container columnset">
				<div class="inlineSearch">
				<% control SearchForm %>
					<form $FormAttributes>
					<% control FieldMap %>
						$Search 
						<input type="submit" title="Search" value="Search" name="action_results" id="SearchForm_SearchForm_action_results" class="action ">
					<% end_control %>
					</form>
				<% end_control %>
				</div>
				$Layout
			</div>
		</div>
		
		<div id="footer">
			<div class="container columnset">
				<div class="column twelve">
					<p><% _t('COPYRIGHT', 'Copyright') %> &copy; $Now.Year | <% _t('POWEREDBY', 'Powered by') %> <a href="http://silverstripe.org">SilverStripe Open Source CMS</a></p>
					<% include FooterMenu %>
				</div>
			</div>
		</div>
		
		<input type="hidden" id="SS_ID" value="$SecurityID" />
		<input type="hidden" id="SS_MEMBER" data-object='$MemberDetails' />
	</body>
</html>
