        <% if Children %>
          <div class="well sidebar-nav">
            <ul class="nav nav-list">
              <li class="nav-header">$Level(1).Title</li>
              <% control Menu(2) %>
              <li class="$LinkingMode"><a href="$Link" title="$Title">$MenuTitle</a></li>
              <% end_control %>
            </ul>
          </div><!--/.well -->
       <% end_if %>
