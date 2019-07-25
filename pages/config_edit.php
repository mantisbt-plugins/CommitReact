<?php

auth_reauthenticate();

access_ensure_global_level(config_get('manage_plugin_threshold'));

plugin_config_set('set_fixed_in_version', gpc_get_int('set_fixed_in_version', 1));
plugin_config_set('attach_tags', gpc_get_string('attach_tags', ''));
plugin_config_set('unattach_tags', gpc_get_string('unattach_tags', ''));
plugin_config_set('resolved_attach_tags', gpc_get_string('resolved_attach_tags', ''));
plugin_config_set('resolved_unattach_tags', gpc_get_string('resolved_unattach_tags', ''));

print_successful_redirect(plugin_page('config', TRUE));
