<div id="picasa_holder">
	<% control PicasaAlbumList %>
		<a href='$Top.Link/viewalbum/$Id'>
			<img src="$Thumb" alt="$Description" <% if ShowDescription %>title="$Description"<% end_if %>/>
		</a>
	<% end_control %>
</div>