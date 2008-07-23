<?php

final class Config
{
    // The name of the wiki
    const WIKI_NAME = 'eWiki';

    // The absolute path of the wiki (no trailing slash)
    const PATH = '';

    // The template for the wiki (see templates/)
    const TEMPLATE = 'archaic';

    // The path to the git repository
    const GIT_PATH = '/srv/git/wiki.git';

    // The branch that should be used
    const GIT_BRANCH = 'master';

    // The locale used by the wiki
    const LOCALE = 'en_US.UTF-8';

    // The time zone of the wiki
    const TIMEZONE = 'GMT';

    // The default maximum image width
    const IMAGE_WIDTH = 640;

    // The default maximum image height
    const IMAGE_HEIGHT = 480;

}

