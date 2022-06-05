<?php

namespace Brunocfalcao\Chrono;

use Brunocfalcao\Cerebrus\Cerebrus;

/**
 * Chrono is a codebase execution duration measurement utility.
 * You can measure execution times, and also use different execution session
 * contexts if you like.
 */
class Chrono
{
    public static function __callStatic($method, $args)
    {
        return ChronoService::new()->{$method}(...$args);
    }
}

class ChronoService
{
    /**
     * A possible category where the measurement is checked at.
     * You can have several categories on your codebase to measure different
     * measurements at the same time.
     *
     * @var string
     */
    protected $category = 'default';

    protected $decimals = 3;

    public function __construct()
    {
        //
    }

    public static function new(...$args)
    {
        return new self(...$args);
    }

    /**
     * Sets a category to differenciate between different chronos being
     * mesuared at the same time.
     *
     * @param  string  $category
     * @return $this
     */
    public function category(string $category)
    {
        $this->category = $category;

        return $this;
    }

    /**
     * Starts a new chronos execution measurement.
     *
     * @return void
     */
    public function start()
    {
        $tick = $this->tick();
    }

    public function elapsed()
    {
        return round($this->now() - $this->get(), $this->decimals);
    }

    public function stop()
    {
        $value = round($this->now() - $this->get(), $this->decimals);

        (new Cerebrus())->unset($this->key());

        return $value;
    }

    public function hasCategory(string $category)
    {
        $session = new Cerebrus();

        return collect($session->all())
               ->has('chrono:'.$category.':'.$session->getId().':tick');
    }

    /**
     * Returns the exact computed system time.
     *
     * @return float
     */
    protected function now()
    {
        return microtime(true);
    }

    /**
     * Sets a time value into session and calls the callable.
     * The session variable is computed given the session id and the
     * category.
     *
     * @return $this
     */
    protected function tick()
    {
        $tick = $this->now();

        $session = new Cerebrus();

        $session->set($this->key(), $tick);

        return $tick;
    }

    /**
     * Gets a time value from session, or from the static attribute.
     *
     * @return float
     */
    protected function get()
    {
        return (new Cerebrus())->get($this->key());
    }

    protected function key()
    {
        return 'chrono:'.
                      strtolower($this->category).
                      ':'.
                      (new Cerebrus())->getId().
                      ':tick';
    }
}
