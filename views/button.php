<style>
	.locker,
	.locker-loader {
		position: absolute;
		top: 0;
		left: 0;
	}

	.locker {
		z-index: 1000;
		opacity: 0.8;
		background-color: white;
		-ms-filter: "progid:DXImageTransform.Microsoft.Alpha(Opacity=80)";
		filter: alpha(opacity=80);
	}

	.locker-loader {
		z-index: 1001;
		background: url(../../../../wp-includes/images/wpspin.gif) no-repeat center center;
	}

	#smodinrewriter {
		margin: auto;
	}

	#smodinrewriter > #smodinrewriter-button {
		
	}

	#smodinrewriter-modal, #smodinrewriter-modal > .section {
		display: none;
	}
</style>

<div id="smodinrewriter">
	<input type="button" class="button button-primary" value="<?php _e( 'Smodin Rewrite', 'smodinrewriter' );?>" id="smodinrewriter-button">
</div>

<div id="smodinrewriter-modal" class="smodinrewriter-dialog" title="<?php _e( 'Smodin Rewrite', 'smodinrewriter' );?>">
	<div class="smodinrewriter-error section"></div>
	<div class="smodinrewriter-success section"></div>
</div>