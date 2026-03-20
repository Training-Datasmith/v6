<?php

declare (strict_types=1);
/**
 * This helper retrieves stats from a /mail/send API call
 */
namespace Send_Grid\Stats;

use DateTime;
use Exception;
/**
 * This class is used to retrieve stats from a /mail/send API call
 *
 * @package SendGrid\Mail
 */
class Stats
{
    /** @var string Expected date format */
    public const DATE_FORMAT = 'Y-m-d';
    /** @var string[] Available sort options */
    public const OPTIONS_SORT_DIRECTION = ['asc', 'desc'];
    /** @var string[] Available aggregate options */
    public const OPTIONS_AGGREGATED_BY = ['day', 'week', 'month'];
    /** @var string Starting date */
    private $start_date;
    /** @var string|null End date (optional) */
    private $end_date;
    /** @var string|null Desired aggregate option (optional) */
    private $aggregated_by;
    /**
     * Stats constructor
     *
     * @param string $startDate    YYYY-MM-DD
     * @param string $endDate      YYYY-MM-DD
     * @param string $aggregatedBy day|week|month
     * @throws Exception
     */
    public function __construct($start_date, $end_date = null, $aggregated_by = null)
    {
        $this->validate_date_format($start_date);
        if (null !== $end_date) {
            $this->validate_date_format($end_date);
        }
        if (null !== $aggregated_by) {
            $this->validate_options('aggregatedBy', $aggregated_by, self::OPTIONS_AGGREGATED_BY);
        }
        $this->start_date = $start_date;
        $this->end_date = $end_date;
        $this->aggregated_by = $aggregated_by;
    }
    /**
     * Retrieve global stats parameters, start date, end date and
     * aggregated by
     */
    public function get_global(): array
    {
        return ['start_date' => $this->start_date, 'end_date' => $this->end_date, 'aggregated_by' => $this->aggregated_by];
    }
    /**
     * Retrieve an array of categories
     *
     * @param array $categories
     *
     * @return array
     * @throws Exception
     */
    public function get_category($categories)
    {
        $this->validate_numeric_array('categories', $categories);
        $stats = $this->get_global();
        $stats['categories'] = $categories;
        return $stats;
    }
    /**
     * Retrieve global stats parameters, start date, end date and
     * aggregated for the given set of subusers
     *
     * @param array $subusers Subuser accounts
     *
     * @return array
     * @throws Exception
     */
    public function get_subuser($subusers)
    {
        $this->validate_numeric_array('subusers', $subusers);
        $stats = $this->get_global();
        $stats['subusers'] = $subusers;
        return $stats;
    }
    /**
     * Retrieve global stats parameters, start date, end date,
     * aggregated by, sort by metric, sort by direction, limit
     * and offset
     *
     * @param string  $sortByMetric    blocks|bounce_drops|bounces|
     *                                 clicks|deferred|delivered|
     *                                 invalid_emails|opens|processed|
     *                                 requests|spam_report_drops|
     *                                 spam_reports|unique_clicks|
     *                                 unique_opens|unsubscribe_drops|
     *                                 unsubscribes
     * @param string  $sortByDirection asc|desc
     * @param integer $limit           The number of results to return
     * @param integer $offset          The point in the list to begin
     *                                 retrieving results
     *
     * @return array
     * @throws Exception
     */
    public function get_sum($sort_by_metric = 'delivered', $sort_by_direction = 'desc', $limit = 5, $offset = 0)
    {
        $this->validate_options('sortByDirection', $sort_by_direction, self::OPTIONS_SORT_DIRECTION);
        $this->validate_integer('limit', $limit);
        $this->validate_integer('offset', $offset);
        $stats = $this->get_global();
        $stats['sort_by_metric'] = $sort_by_metric;
        $stats['sort_by_direction'] = $sort_by_direction;
        $stats['limit'] = $limit;
        $stats['offset'] = $offset;
        return $stats;
    }
    /**
     * Retrieve monthly stats by subuser
     *
     * @param string  $subuser         Subuser account
     * @param string  $sortByMetric    blocks|bounce_drops|bounces|
     *                                 clicks|deferred|delivered|
     *                                 invalid_emails|opens|processed|
     *                                 requests|spam_report_drops|
     *                                 spam_reports|unique_clicks|
     *                                 unique_opens|unsubscribe_drops|
     *                                 unsubscribes
     * @param string  $sortByDirection asc|desc
     * @param integer $limit           The number of results to return
     * @param integer $offset          The point in the list to begin
     *                                 retrieving results
     *
     * @throws Exception
     */
    public function get_subuser_monthly($subuser = null, $sort_by_metric = 'delivered', $sort_by_direction = 'desc', $limit = 5, $offset = 0): array
    {
        $this->validate_options('sortByDirection', $sort_by_direction, self::OPTIONS_SORT_DIRECTION);
        $this->validate_integer('limit', $limit);
        $this->validate_integer('offset', $offset);
        return ['date' => $this->start_date, 'subuser' => $subuser, 'sort_by_metric' => $sort_by_metric, 'sort_by_direction' => $sort_by_direction, 'limit' => $limit, 'offset' => $offset];
    }
    /**
     * Validate the date format
     *
     * @param string $date YYYY-MM-DD
     *
     * @throws Exception
     */
    protected function validate_date_format($date)
    {
        if (false === DateTime::create_from_format(self::DATE_FORMAT, $date)) {
            throw new Exception('Date must be in the YYYY-MM-DD format.');
        }
    }
    /**
     * Validate options
     *
     * @param string $name    Name of option
     * @param string $value   Value of option
     * @param array  $options Array of options
     *
     * @throws Exception
     */
    protected function validate_options(string $name, $value, $options)
    {
        if (!in_array($value, $options)) {
            throw new Exception($name . ' must be one of: ' . implode(', ', $options));
        }
    }
    /**
     * Validate integer
     *
     * @param string  $name  Name as a string
     * @param integer $value Value as an integer
     *
     * @throws Exception
     */
    protected function validate_integer(string $name, $value)
    {
        if (!is_integer($value)) {
            throw new Exception($name . ' must be an integer.');
        }
    }
    /**
     * Validate a numeric array
     *
     * @param string $name  Name as a string
     * @param array  $value Value as an array of integers
     *
     * @throws Exception
     */
    protected function validate_numeric_array(string $name, $value)
    {
        if (!\is_array($value) || empty($value) || !$this->is_numeric($value)) {
            throw new Exception($name . ' must be a non-empty numeric array.');
        }
    }
    /**
     * Determine if the array is numeric
     *
     * @param array $array Array of values
     */
    protected function is_numeric(array $array): bool
    {
        return \array_keys($array) === range(0, \count($array) - 1);
    }
}