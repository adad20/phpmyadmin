<?php
/**
 * Displays git revision
 *
 * @package PhpMyAdmin
 */
declare(strict_types=1);

namespace PhpMyAdmin\Display;

use PhpMyAdmin\Config;
use PhpMyAdmin\Core;
use PhpMyAdmin\Response;
use PhpMyAdmin\Template;
use PhpMyAdmin\Util;

/**
 * PhpMyAdmin\Display\GitRevision class
 *
 * @package PhpMyAdmin
 */
class GitRevision
{
    /**
     * @var Response
     */
    private $response;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var Template
     */
    private $template;

    /**
     * GitRevision constructor.
     * @param Response $response Response instance
     * @param Config   $config   Config instance
     * @param Template $template Template instance
     */
    public function __construct($response, $config, $template)
    {
        $this->response = $response;
        $this->config = $config;
        $this->template = $template;
    }

    /**
     * Returns details about the current Git commit revision
     *
     * @return string HTML
     */
    public function display(): string
    {
        // load revision data from repo
        $this->config->checkGitRevision();

        if (! $this->config->get('PMA_VERSION_GIT')) {
            $this->response->setRequestStatus(false);
            return '';
        }

        // if using a remote commit fast-forwarded, link to GitHub
        $commitHash = substr(
            $this->config->get('PMA_VERSION_GIT_COMMITHASH'),
            0,
            7
        );
        $commitHash = '<strong title="'
            . htmlspecialchars($this->config->get('PMA_VERSION_GIT_MESSAGE'))
            . '">' . $commitHash . '</strong>';
        if ($this->config->get('PMA_VERSION_GIT_ISREMOTECOMMIT')) {
            $commitHash = '<a href="'
                . Core::linkURL(
                    'https://github.com/phpmyadmin/phpmyadmin/commit/'
                    . $this->config->get('PMA_VERSION_GIT_COMMITHASH')
                )
                . '" rel="noopener noreferrer" target="_blank">' . $commitHash . '</a>';
        }

        $branch = $this->config->get('PMA_VERSION_GIT_BRANCH');
        if ($this->config->get('PMA_VERSION_GIT_ISREMOTEBRANCH')) {
            $branch = '<a href="'
                . Core::linkURL(
                    'https://github.com/phpmyadmin/phpmyadmin/tree/'
                    . $this->config->get('PMA_VERSION_GIT_BRANCH')
                )
                . '" rel="noopener noreferrer" target="_blank">' . $branch . '</a>';
        }
        if ($branch !== false) {
            $branch = sprintf(__('%1$s from %2$s branch'), $commitHash, $branch);
        } else {
            $branch = $commitHash . ' (' . __('no branch') . ')';
        }

        $committer = $this->config->get('PMA_VERSION_GIT_COMMITTER');
        $author = $this->config->get('PMA_VERSION_GIT_AUTHOR');

        $name = __('Git revision:') . ' '
            . $branch . ',<br> '
            . sprintf(
                __('committed on %1$s by %2$s'),
                Util::localisedDate(strtotime($committer['date'])),
                '<a href="' . Core::linkURL(
                    'mailto:' . htmlspecialchars($committer['email'])
                ) . '">'
                . htmlspecialchars($committer['name']) . '</a>'
            )
            . ($author != $committer
                ? ', <br>'
                . sprintf(
                    __('authored on %1$s by %2$s'),
                    Util::localisedDate(strtotime($author['date'])),
                    '<a href="' . Core::linkURL(
                        'mailto:' . htmlspecialchars($author['email'])
                    ) . '">'
                    . htmlspecialchars($author['name']) . '</a>'
                )
                : '');

        return $this->template->render('list/item', [
            'content' => $name,
            'id' => 'li_pma_version_git',
            'class' => null,
            'url' => [
                'href' => null,
                'target' => null,
                'id' => null,
                'class' => null,
            ],
            'mysql_help_page' => null,
        ]);
    }
}
