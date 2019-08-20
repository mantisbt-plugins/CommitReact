<?php

# Copyright ( c ) 2019 Scott Meesseman
# Licensed under GPL3 

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
        try 
        {
            foreach ( $p_changesets as $t_changeset )
            {
                foreach ( $t_changeset->bugs as $t_bug => $t_bug_id )
                {
                    $this->handle_tags( $t_changeset->user_id, $t_bug_id, $t_changeset->message, plugin_config_get( 'attach_tags' ), plugin_config_get( 'unattach_tags' ) );
                }
            }
        }
        catch ( Exception $e ) {
            log_event( LOG_PLUGIN, "CommitReact: Exception handle_tags post commit - %s", $e->getMessage() );
        }

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


    public function handle_tags( $p_user_id, $p_bug_id, $p_commit_msg, $p_attach_tags, $p_unattach_tags )
    {
        log_event( LOG_PLUGIN, "CommitReact: Handle tags - %d userid %d", $p_bug_id, $p_user_id );
        log_event( LOG_PLUGIN, "CommitReact: Handle tags (attach) - %s", $p_attach_tags);
        log_event( LOG_PLUGIN, "CommitReact: Handle tags (unattach) - %s", $p_unattach_tags );

        #
        # Attach tags
        #
        if ( !is_blank( $p_attach_tags ) )
        {
            $t_tag_strings = explode( config_get( 'tag_separator' ), $p_attach_tags );
            foreach ( $t_tag_strings as $t_tag_string )
            {
                log_event( LOG_PLUGIN, "CommitReact: Process attach tag %s", $t_tag_string );

                $t_tag_string_parts = explode( ':', $t_tag_string );

                if ( count( $t_tag_string_parts ) === 1 ) 
                {
                    $t_tag = tag_get_by_name( $t_tag_string_parts[0] );
                    if ( $t_tag === false ) {
                        if ( access_has_global_level( config_get('tag_create_threshold'), $p_user_id ) ) {
                            $t_tag_id = tag_create($t_tag_string_parts[0], $p_user_id);
                        }
                        else {
                            continue;
                        }
                    }
                    else {
                        $t_tag_id = $t_tag['id'];
                    }
                    if ( !tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                        if ( access_has_global_level( config_get('tag_attach_threshold'), $p_user_id ) ) {
                            tag_bug_attach( $t_tag_id, $p_bug_id, $p_user_id );
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
                                continue;
                            }
                        }
                        else {
                            $t_tag_id = $t_tag['id'];
                        }
                        if ( !tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                            if ( access_has_global_level( config_get('tag_attach_threshold'), $p_user_id ) ) {
                                tag_bug_attach( $t_tag_id, $p_bug_id, $p_user_id );
                            }
                        }
                    }
                }
            }
        }

        #
        # Unattach tags
        #
        if ( !is_blank( $p_unattach_tags ) )
        {
            $t_tag_strings = explode( config_get( 'tag_separator' ), $p_unattach_tags );
            foreach ( $t_tag_strings as $t_tag_string )
            {
                log_event( LOG_PLUGIN, "CommitReact: Process unattach tag %s", $t_tag_string );

                $t_tag_string_parts = explode( ':', $t_tag_string );

                if ( count( $t_tag_string_parts ) === 1 ) 
                {
                    $t_tag = tag_get_by_name( $t_tag_string_parts[0] );
                    if ( $t_tag === false ) {
                        if ( access_has_global_level( config_get('tag_create_threshold'), $p_user_id ) ) {
                            $t_tag_id = tag_create( $t_tag_string_parts[0], $p_user_id );
                        }
                        else {
                            continue;
                        }
                    }
                    else {
                        $t_tag_id = $t_tag['id'];
                    }
                    if ( tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                        if ( access_has_global_level( config_get('tag_detach_threshold'), $p_user_id ) ) {
                            tag_bug_detach( $t_tag_id, $p_bug_id, true, $p_user_id );
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
                                $t_tag_id = tag_create( $t_tag_string_parts[1], $p_user_id );
                            }
                            else {
                                continue;
                            }
                        }
                        else {
                            $t_tag_id = $t_tag['id'];
                        }
                        if ( tag_bug_is_attached( $t_tag_id, $p_bug_id ) ) {
                            if ( access_has_global_level( config_get('tag_detach_threshold'), $p_user_id ) ) {
                                tag_bug_detach( $t_tag_id, $p_bug_id, true, $p_user_id );
                            }
                        }
                    }
                }
            }
        }

        return;
    }


    public function post_commit_fixed( $p_event, $p_fixed_bugs )
    {
        log_event( LOG_PLUGIN, "CommitReact: post commit fixed" );

        foreach ( $p_fixed_bugs as $t_bug_id => $t_changeset ) 
        {
            $t_project_id = bug_get_field( $t_bug_id, 'project_id' );
            if ( $t_project_id == null ) {
                continue;
            }
            
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

            if ( !empty( $t_version ) ) {log_event( LOG_PLUGIN, "CommitReact: 222" ); 
                bug_set_field( $t_bug_id, 'fixed_in_version', $t_version );
            }

            try {
                #$unattach_tags = implode( config_get( 'tag_separator' ), array_unique( array_merge( array( 'bug' ), array( plugin_config_get( 'resolved_unattach_tags' ) ) ) ) );
                $this->handle_tags( $t_changeset->user_id, $t_bug_id, $t_changeset->message, plugin_config_get( 'resolved_attach_tags' ), plugin_config_get( 'resolved_unattach_tags' ) );
            }
            catch ( Exception $e ) {
                log_event( LOG_PLUGIN, "CommitReact: Exception handle_tags post commit resolve - %s", $e->getMessage() );
            }
        }

        return;
    }

}
