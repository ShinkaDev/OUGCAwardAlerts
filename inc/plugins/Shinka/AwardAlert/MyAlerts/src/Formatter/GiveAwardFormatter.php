<?php

/**
 * Alert formatter for approve threads alerts.
 */
class Shinka_AwardAlert_MyAlerts_Formatter_ApproveThreadsFormatter extends MybbStuff_MyAlerts_Formatter_AbstractFormatter
{
    /**
     * Format an alert into it's output string to be used in both the main alerts listing page and the popup.
     *
     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to format.
     *
     * @return string The formatted alert string.
     */
    public function formatAlert(MybbStuff_MyAlerts_Entity_Alert $alert, array $outputAlert)
    {
    	$alertContent = $alert->getExtraDetails();
    	if ($alertContent['reason']) {
            return $this->lang->sprintf(
                $this->lang->shinka_awardalert_give_award_with_reason,
                $alertContent['award'],
                $alertContent['reason']
            );
        }

        return $this->lang->sprintf(
            $this->lang->shinka_awardalert_give_award,
            $alertContent['award']
        );
    }

    /**
     * Init function called before running formatAlert(). Used to load language files and initialize other required
     * resources.
     *
     * @return void
     */
    public function init()
    {
        if (!$this->lang->shinka_awardalert_give_award || 
                !$this->lang->shinka_awardalert_give_award_with_reason) {
            $this->lang->load('shinka_awardalert');
        }
    }

    /**
     * Build a link to an alert's content so that the system can redirect to it.
     *
     * @param MybbStuff_MyAlerts_Entity_Alert $alert The alert to build the link for.
     *
     * @return string The built alert, preferably an absolute link.
     */
    public function buildShowLink(MybbStuff_MyAlerts_Entity_Alert $alert)
    {
    	return get_profile_link($alert->getFromUserId());
    }
}
