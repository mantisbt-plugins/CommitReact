# COMMITREACT CHANGE LOG

## Version 1.2.0 (August 25th, 2019)

### Features

- add support to apply tagging/untagging based on access level of committer

## Version 1.1.1 (August 20th, 2019)

### Bug Fixes

- target version is being set but is throwing exception afterwards before retruning from event

	This is due to global user id not being set correctly, as source-integration plugin unsets the userid before firing the commit events.

- automatic tagging added in v1.1 is not working correctly if target version is not set

## Version 1.1.0 (August 20th, 2019)

### Documentation

- **readme:** udpate issues submit section

### Features

- add support for adding and removing tags on commit/commit-fixed

## Version 1.0.5 (August 8th, 2019)

### Bug Fixes

- tgz release package does not contain the plugin directory as the top level

## Version 1.0.4 (August 3rd, 2019)

### Build System

- **ap:** add gzip tarball to mantisbt and github release assets

## Version 1.0.3 (August 3rd, 2019)

### Documentation

- **README:** update toc

### Features

- show the success redirect when saving config settings

### Bug Fixes

- form security cookie is not set properly

## Version 1.0.2 (July 29th, 2019)

### Build System

- **app-publisher:** set interactive flag to N for non-interactive setting of new version during publish run (compliments of ap v1.10.4 update)
- add config to publishrc for first mantisbt release

### Documentation

- **README:** update ApiExtend badges

## Version 1.0.1 (July 27th, 2019)

### Documentation

- **readme:** update installation section and issues badge links

### Miscellaneous

- Update license to GPLv3

## Version 1.0.0 (July 25th, 2019)

### Chores

- Initial release

