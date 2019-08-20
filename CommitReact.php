<?php

# Copyright ( c ) 2019 Scott Meesseman
# Licensed under GPL3 

$g_cache_commitreact_current_user_id = NO_USER;

class CommitReactPlugin extends MantisPlugin
{
    
    public function register( )
    {
        $this->name = plugin_lang_get( "title" );
        $this->description = plugin_lang_get( "description" );
        $this->page = 'config';

        $this->version = "1.1.0";
        $this->requires = array( 
            "MantisCore" => "2.0.1",
            "Source" => "2.2.0"
        );

        $this->author = "Scott Meesseman";
        $this->contact = "spmeesseman@gmail.com";
        $this->url = "https://github.com/mantisbt-plugins/CommitReact";
    }


    function config( ) 
    {
		return array( 
			'set_fixed_in_version'    => 1,
            'unattach_tags'           => '',
            'attach_tags'             => '',
            'resolved_unattach_tags'  => '',
            'resolved_attach_tags'    => ''
		 );
    }


    public function hooks( )
    {
        return array( 
            #"EVENT_SOURCE_PRECOMMIT" => "pre_commit",
            "EVENT_SOURCE_COMMITS" => "post_commit",
            "EVENT_SOURCE_FIXED" => "post_commit_fixed"
        );
    }


    public function pre_commit( $event )
    {
        return array();
    }


    public function post_commit( $p_event, $p_changesets )
    {
        log_event( LOG_PLUGIN, "CommitReact: post_commit" );

        $t_resolved_threshold = config_get('bug_resolved_status_threshold');
        #$t_fixed_threshold = config_get('bug_resolution_fixed_threshold');
        #$t_notfixed_threshold = config_get('bug_resolution_not_fixed_threshold');
    
        try {
            foreach ( $p_changesets as $t_changeset ) 
            {
                foreach ( $t_changeset->bugs as $t_key => $t_bug_id ) 
                {
                    $this->cr_set_user($t_bug_id, $t_changeset);
                    $t_bug = bug_get($t_bug_id);
                    if ($t_bug->status < $t_resolved_threshold ) {
                        $this->handle_tags( $t_changeset->user_id, $t_bug_id, $t_changeset->message, plugin_config_get( 'attach_tags' ), plugin_config_get( 'unattach_tags' ) );
                    }
                    else {
                        log_event( LOG_PLUGIN, "CommitReact: bug id %d is resolved, skip event", $t_bug_id );
                    }
                }
            }
        }
        catch ( Exception $e ) {
            log_event( LOG_PLUGIN, "CommitReact: Exception handle_tags post commit - %s", $e->getMessage() );
        }

        $this->cr_reset_user();

        return;
    }


    public function post_commit_fixed( $p_event, $p_fixed_bugs )
    {
        log_event( LOG_PLUGIN, "CommitReact: post_commit_fixed" );

        foreach ( $p_fixed_bugs as $t_bug_id => $t_changeset ) 
        {
            $t_project_id = bug_get_field( $t_bug_id, 'project_id' );
            if ( $t_project_id == null ) {
                continue;
            }

            $this->cr_set_user($t_bug_id, $t_changeset);

            if ( plugin_config_get( 'set_fixed_in_version' ) == 1 ) 
            {
                $t_version = null;
                $t_versions = version_get_all_rows( $t_project_id, VERSION_FUTURE );
                foreach ( $t_versions as $v ) 
                {
                    if ( $t_version != null && strpos( $t_version, '.' ) != false && version_compare( $v['version'], $t_version ) < 0 ) {
                        $t_version = $v['version'];
                    }
                    else if ( $t_version != null && strpos( $t_version, '.' ) == false && strcmp( $v['version'], $t_version ) < 0 ) {
                        $t_version = $v['version'];
                    }
                    else if ( $t_version == null ) {
                        $t_version = $v['version'];
                    }
                }

                if ( !is_blank( $t_version ) ) {
                    bug_set_field( $t_bug_id, 'fixed_in_version', $t_version );
                }
            }
            
            try {
                $this->handle_tags( $t_changeset->user_id, $t_bug_id, $t_changeset->message, plugin_config_get( 'resolved_attach_tags' ), plugin_config_get( 'resolved_unattach_tags' ) );
            }
            catch ( Exception $e ) {
                log_event( LOG_PLUGIN, "CommitReact: Exception handle_tags post commit resolve - %s", $e->getMessage() );
            }
        }

        $this->cr_reset_user();

        return;
    }


    private function get_commit_subject( $p_commit_msg )
    {
        $t_subject = '';
        if (strstr( $p_commit_msg, ':' ) === false ) {
            return $t_subject;
        }

        $t_end = strpos( $p_commit_msg, ':' );

        # scope
        if (strstr( $p_commit_msg, '(' ) === false ) {
            $t_end = strpos( $p_commit_msg, '(' );
        }
        
        $t_subject = substr(0, $t_end);

        #subject should contain no spaces
        if (strstr( $t_subject, ' ' ) === false ) {
            return '';
        }

        return strtolower( trim( $t_subject ) );
    }


    private function cr_reset_user()
    {
        global $g_cache_commitreact_current_user_id;
        global $g_cache_current_user_id;
        $g_cache_current_user_id = $g_cache_commitreact_current_user_id;
        log_event( LOG_PLUGIN, "CommitReact: reset user id - %d", $g_cache_current_user_id );
    }


    private function cr_set_user($p_bug_id, $p_changeset)
    {
        global $g_cache_commitreact_current_user_id;
        global $g_cache_current_user_id;
        $g_cache_commitreact_current_user_id = $g_cache_current_user_id;

        $t_handle_bug_threshold = config_get( 'handle_bug_threshold' );
    
        #
        # From source-integration plugin
        #
        # Determine the Mantis user to associate with the issue referenced in
		# the changeset:
		# - use Author if they can handle the issue
		# - use Committer if not
		# - if Committer can't handle issue either, it will not be resolved.
		# This is used to generate the history entries and set the bug handler
		# if the changeset fixes the issue.
		$t_user_id = null;
		if ( $p_changeset->user_id > 0 ) {
			$t_can_handle_bug = access_has_bug_level( $t_handle_bug_threshold, $p_bug_id, $p_changeset->user_id );
			if( $t_can_handle_bug ) {
				$t_user_id = $p_changeset->user_id;
			}
		}
		$t_handler_id = $t_user_id;
		if( $t_handler_id === null && $p_changeset->committer_id > 0 ) {
			$t_user_id = $p_changeset->committer_id;
			$t_can_handle_bug = access_has_bug_level( $t_handle_bug_threshold, $p_bug_id, $t_user_id );
			if( $t_can_handle_bug ) {
				$t_handler_id = $t_user_id;
			}
		}

		if ( !is_null( $t_user_id ) ) {
			$g_cache_current_user_id = $t_user_id;
		} else if ( !is_null( $g_cache_commitreact_current_user_id ) ) {
			$g_cache_current_user_id = $g_cache_commitreact_current_user_id;
		} else {
			$g_cache_current_user_id = 0;
        }
        
        log_event( LOG_PLUGIN, "CommitReact: set user id - %d", $g_cache_current_user_id );

        return;
    }


    private function handle_tags( $p_user_id, $p_bug_id, $p_commit_msg, $p_attach_tags, $p_unattach_tags )
    {
        log_event( LOG_PLUGIN, "CommitReact: Handle tags - bugid %d userid %d", $p_bug_id, $p_user_id );
        $this->handle_tag_type( 'attach', $p_user_id, $p_bug_id, $p_commit_msg, $p_attach_tags );
        $this->handle_tag_type( 'detach', $p_user_id, $p_bug_id, $p_commit_msg, $p_unattach_tags );
        return;
    }


    private function handle_tag_type( $p_type, $p_user_id, $p_bug_id, $p_commit_msg, $p_tags )
    {
        if ( !is_blank( $p_tags ) )
        {
            log_event( LOG_PLUGIN, "CommitReact: Handle tags (%s) - %s", $p_type, $p_tags);

            $t_tag_strings = explode( config_get( 'tag_separator' ), $p_tags );
            
            foreach ( $t_tag_strings as $t_tag_string )
            {   
                log_event( LOG_PLUGIN, "CommitReact: Process tag %s", $t_tag_string );

                $t_tag_string_parts = explode( ':', $t_tag_string );

                if ( count( $t_tag_string_parts ) === 1 ) 
                {
                    $t_tag = tag_get_by_name( $t_tag_string_parts[0] );
                    if ( $t_tag === false ) {
                        if ( access_has_global_level( config_get('tag_create_threshold'), $p_user_id ) ) {
                            $t_tag_id = tag_create($t_tag_string_parts[0], $p_user_id);
                        }
                        else {
                            log_event( LOG_PLUGIN, "CommitReact: Access level cannot create tag", $t_tag_string );
                            continue;
                        }
                    }
                    else {
                        $t_tag_id = $t_tag['id'];
                    }
                    
                    if ($p_type == 'attach') {
                        if ( !tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                            if ( access_has_global_level( config_get('tag_attach_threshold'), $p_user_id ) ) {
                                tag_bug_attach( $t_tag_id, $p_bug_id, $p_user_id );
                            }
                        }
                    }
                    else if ($p_type == 'detach') {
                        if ( tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                            if ( access_has_global_level( config_get('tag_detach_threshold'), $p_user_id ) ) {
                                tag_bug_detach( $t_tag_id, $p_bug_id, true, $p_user_id );
                            }
                        }
                    }
                }
                else if ( count( $t_tag_string_parts ) === 2 ) 
                {
                    if ( !is_blank( $t_tag_string_parts[0] ) && strtolower( trim( $t_tag_string_parts[0] ) ) === $this->get_commit_subject( $p_commit_msg ) )
                    {
                        $t_tag = tag_get_by_name( $t_tag_string_parts[1] );
                        if ( $t_tag === false ) {
                            if ( access_has_global_level( config_get('tag_create_threshold'), $p_user_id ) ) {
                                $t_tag_id = tag_create($t_tag_string_parts[0], $p_user_id );
                            }
                            else {
                                log_event( LOG_PLUGIN, "CommitReact: Access level cannot create tag", $t_tag_string );
                                continue;
                            }
                        }
                        else {
                            $t_tag_id = $t_tag['id'];
                        }
                        if ($p_type == 'attach') {
                            if ( !tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                                if ( access_has_global_level( config_get('tag_attach_threshold'), $p_user_id ) ) {
                                    tag_bug_attach( $t_tag_id, $p_bug_id, $p_user_id );
                                }
                            }
                        }
                        else if ($p_type == 'detach') {
                            if ( tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                                if ( access_has_global_level( config_get('tag_detach_threshold'), $p_user_id ) ) {
                                    tag_bug_detach( $t_tag_id, $p_bug_id, true, $p_user_id );
                                }
                            }
                        }
                    }
                }
            }
        }
    }

}
