<?php

form_security_validate( 'plugin_CommitReact_config_edit' );
auth_reauthenticate();

access_ensure_global_level(config_get('manage_plugin_threshold'));

plugin_config_set('set_fixed_in_version', gpc_get_int('set_fixed_in_version', 1));
plugin_config_set('attach_tags', gpc_get_string('attach_tags', ''));
plugin_config_set('unattach_tags', gpc_get_string('unattach_tags', ''));
plugin_config_set('resolved_attach_tags', gpc_get_string('resolved_attach_tags', ''));
plugin_config_set('resolved_unattach_tags', gpc_get_string('resolved_unattach_tags', ''));

form_security_purge( 'plugin_CommitReact_config_edit' );

$t_redirect_url = plugin_page('config', TRUE);

layout_page_header( null, $t_redirect_url );
layout_page_begin();
html_operation_successful( $t_redirect_url );
layout_page_end();
