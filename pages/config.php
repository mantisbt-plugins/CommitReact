<?php
auth_reauthenticate();
access_ensure_global_level(config_get('manage_plugin_threshold'));

layout_page_header(plugin_lang_get('title'));
layout_page_begin(__FILE__);
print_manage_menu( 'manage_plugin_page.php' );

$set_fixed_in_version = plugin_config_get('set_fixed_in_version');
$attach_strings = plugin_config_get('resolved_attach_strings');
$unattach_strings = plugin_config_get('resolved_unattach_strings');
$resolved_attach_strings = plugin_config_get('resolved_attach_strings');
$resolved_unattach_strings = plugin_config_get('resolved_unattach_strings');

?>

<br />

<div class="col-xs-12 col-md-8 col-md-offset-2">
	<div class="space-10"></div>
	<div id="config-div" class="form-container">
		<form id="config-form" method="post" action="<?php echo plugin_page('config_edit') ?>">
			<?php echo form_security_field('plugin_CommitReact_config_edit') ?>
			<div class="widget-box widget-color-blue2">
				<div class="widget-header widget-header-small">
					<h4 class="widget-title lighter">
						<i class="ace-icon fa fa-cogs"></i>
						<?php echo plugin_lang_get('title') . ': ' . plugin_lang_get('config') ?>
					</h4>
				</div>
				<div class="widget-body">
					<div class="widget-main no-padding">
						<div class="form-container">
							<div class="table-responsive">
								<table class="table table-bordered table-condensed table-striped">
									<fieldset>
										<tr>
											<td class="category">
												<?php echo plugin_lang_get('set_fixed_in_version'); ?>
												<span class="small"><br><?php echo plugin_lang_get('set_fixed_in_version_desc') ?></span>
											</td>
											<td>
												<select name="set_fixed_in_version">
													<option value="1" <?php echo check_selected($set_fixed_in_version, 1); ?>>Yes</option>
													<option value="0" <?php echo check_selected($set_fixed_in_version, 0); ?>>No</option>
												</select>
											</td>
										</tr>
										<tr>
											<td class="category">
												<?php echo plugin_lang_get('attach_tags') ?>
												<span class="small"><br><?php echo plugin_lang_get('tags_desc'); ?></span><?php echo config_get('tag_separator'); ?>
											</td>
											<td>
												<input size="100" maxlength="250" name="attach_tags" value="<?php echo plugin_config_get('attach_tags'); ?>">
											</td>
										</tr>
										<tr>
											<td class="category">
												<?php echo plugin_lang_get('unattach_tags') ?>
												<span class="small"><br><?php echo plugin_lang_get('tags_desc'); ?></span><?php echo config_get('tag_separator'); ?>
											</td>
											<td>
												<input size="100" maxlength="250" name="unattach_tags" value="<?php echo plugin_config_get('unattach_tags'); ?>">
											</td>
										</tr>
										<tr>
											<td class="category">
												<?php echo plugin_lang_get('resolved_attach_tags') ?>
												<span class="small"><br><?php echo plugin_lang_get('tags_desc'); ?></span><?php echo config_get('tag_separator'); ?>
											</td>
											<td>
												<input size="100" maxlength="250" name="resolved_attach_tags" value="<?php echo plugin_config_get('resolved_attach_tags'); ?>">
											</td>
										</tr>
										<tr>
											<td class="category">
												<?php echo plugin_lang_get('resolved_unattach_tags') ?>
												<span class="small"><br><?php echo plugin_lang_get('tags_desc'); ?></span><?php echo config_get('tag_separator'); ?>
											</td>
											<td>
												<input size="100" maxlength="250" name="resolved_unattach_tags" value="<?php echo plugin_config_get('resolved_unattach_tags'); ?>">
											</td>
										</tr>
									</fieldset>
								</table>

							</div>
						</div>
					</div>

					<div class="widget-toolbox padding-8 clearfix">
						<input type="submit" name="submit" class="btn btn-primary btn-white btn-round" value="<?php echo plugin_lang_get('update_config') ?>" />
					</div>
				</div>
			</div>
		</form>
	</div>
	<div class="space-10"></div>
</div>

<?php
layout_page_end();
