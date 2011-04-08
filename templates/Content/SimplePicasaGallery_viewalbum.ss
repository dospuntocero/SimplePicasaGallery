<div id="picasa_holder">
	<% control AlbumPictures %>
		<a href="$Image" class="jspopup" rel="jspopup">
			<img src="$Thumb" alt="$Description" <% if ShowDescription %>title="$Description"<% end_if %>/>
		</a>
	<% end_control %>
</div>