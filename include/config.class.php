<?php

final class Config
{

    // The name of the wiki
    const WIKI_NAME = 'eWiki';

    // The absolute path of the wiki (no trailing slash)
    const PATH = '';

    // The path to the git repository
    const GIT_PATH = '/srv/git/wiki.git';

    // The branch that should be used
    const GIT_BRANCH = 'master';

    // Name of the author used for edits via the web interface
    const AUTHOR_NAME = 'Anonymous Coward';

    // Mail of the author used for edits via the web interface
    const AUTHOR_MAIL = 'anonymous@wiki.invalid';

    // The locale used by the wiki
    const LOCALE = 'en_US.UTF-8';

    // The time zone of the wiki
    const TIMEZONE = 'GMT';

}

