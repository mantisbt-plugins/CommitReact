<?php

# Copyright (c) 2019 Scott Meesseman
# Licensed under GPL3 

class CommitReactPlugin extends MantisPlugin
{
    public function register()
    {
        $this->name = plugin_lang_get("title");
        $this->description = plugin_lang_get("description");
        $this->page = 'config';

        $this->version = "1.0.4";
        $this->requires = array(
            "MantisCore" => "2.0.1",
            "Source" => "2.2.0"
        );

        $this->author = "Scott Meesseman";
        $this->contact = "spmeesseman@gmail.com";
        $this->url = "https://github.com/mantisbt-plugins/CommitReact";
    }

    function config() 
    {
		return array(
			'set_fixed_in_version'    => 1,
            'unattach_tags'           => '',
            'attach_tags'             => '',
            'resolved_unattach_tags'  => '',
            'resolved_attach_tags'    => ''
		);
    }

    public function hooks()
    {
        return array(
            #"EVENT_SOURCE_PRECOMMIT" => "pre_commit",
            "EVENT_SOURCE_COMMITS" => "post_commit",
            "EVENT_SOURCE_FIXED" => "post_commit_fixed"
        );
    }

    public function pre_commit($event)
    {
        return array();
    }

    public function post_commit($event, $changesets)
    {
        #$this->handle_tags($changesets, plugin_config_get('attach_tags'), plugin_config_get('unattach_tags'));
        return;
    }

    public function handle_tags($changesets, $attach_tags, $unattach_tags)
    {
        foreach ($changesets as $changeset) 
        {
            foreach ($changeset->bugs as $bug)
            {
                #
                # Attach tags
                #
                if (!is_blank($attach_tags))
                {
                    $tag_strings = explode(config_get('tag_separator'), $attach_tags);
                    foreach ($tag_strings as $tag_string)
                    {
                        $tag = tag_get_by_name($tag_string);
                        if ($tag === false) {
                            continue;
                        }
                        if (!tag_bug_is_attached($tag['id'], $bug['id'])) {
                            tag_bug_attach($tag['id'], $bug['id']);
                        }
                    }
                }
                #
                # Unattach tags
                #
                if (!is_blank($unattach_tags))
                {
                    $tag_strings = explode(config_get('tag_separator'), $unattach_tags);
                    foreach ($tag_strings as $tag_string)
                    {
                        $tag = tag_get_by_name($tag_string);
                        if ($tag === false) {
                            continue;
                        }
                        if (tag_bug_is_attached($tag['id'], $bug['id'])) {
                            tag_bug_detach($tag['id'], $bug['id'], true);
                        }
                    }
                }
            }
        }

        return;
    }

    public function post_commit_fixed($event, $fixed_bugs)
    {
        foreach ($fixed_bugs as $bug_id => $changeset) 
        {
            $project_id = bug_get_field($bug_id, 'project_id');
            if ($project_id == null) {
                continue;
            }
            
            $version = null;
            $versions = version_get_all_rows($project_id, VERSION_FUTURE);
            foreach ($versions as $v) 
            {
                if ($version != null && strpos($version, '.') != false && version_compare($v['version'], $version) < 0) {
                    $version = $v['version'];
                }
                else if ($version != null && strpos($version, '.') == false && strcmp($v['version'], $version) < 0) {
                    $version = $v['version'];
                }
                else if ($version == null) {
                    $version = $v['version'];
                }
            }

            if ($version == null || $version == '') {
                continue;
            }

            bug_set_field($bug_id, 'fixed_in_version', $version);
        }

        #$unattach_tags = array_unique(array_merge(array('bug'), array(plugin_config_get('resolved_unattach_tags')));
        #$this->handle_tags($fixed_bugs, plugin_config_get('resolved_attach_tags'), implode(config_get('tag_separator'), $unattach_tags));

        return;
    }
}
