<?php
namespace Citizenzet\Php\Core\Components;

/**
 * A simple progress bar to keep users informed during long-running php scripts.
 *
 * $bar = new ProgressBar(count($items));
 *
 * foreach ($items as $item) {
 *      $bar->update();
 *      // import/update/delete item
 *  }
 *
 **/

class ProgressBar {

    private $done;        // done items
    private $total;       // total items
    private $bar_length;  // length of progress bar
    private $perc;        // percentage done

    /**
     *  Constructor.
     *  @param int  Total number of items
     *  @param int  Length of progressBar (in console characters)
     */
    function __construct($total, $bar_length = 70) {
        $this->done = 0;
        $this->perc = 0;
        $this->perc = 0;
        $this->total = $total;
        $this->bar_length = $bar_length;
    }

    /**
     *  Set the length of the bar.
     *  @param int  New length of progressBar
     */
    function set_bar_length($bar_length) {
        $this->bar_length = $bar_length;
        $this->draw();
    }

    /**
     *  Get the length of the bar.
     *  @return int Current ProgressBar length
     */
    function get_bar_length() {
        return $this->bar_length;
    }

    /**
     *  Set the total number of items.
     *  @param int  New total number of items
     */
    function set_total($total) {
        $this->total = $total;
        $this->draw();
    }

    /**
     *  Get the total number of items.
     *  @return int Total number of items
     */
    function get_total() {
        return $this->total;
    }

    /**
     *  Update ProgressBar.
     *
     *  Call with no argumens to automatically increase by 1.
     *  Call with a number, to move ProgressBar to that number.
     *
     *  @param int  Done items
     */
    function update($done = FALSE) {
        if ($done === FALSE) {
            $this->done += 1;
        }
        else {
            $this->done = $done;
        }
        $this->draw();
    }

    /**
     *  Draw ProgressBar.
     *
     *  Private function that draws the ProgressBar.
     */
    private function draw() {
        $this->perc = floor(($this->done / $this->total) * 100);
        $bar_perc = floor(($this->done / $this->total) * $this->bar_length);
        $left = $this->bar_length - $bar_perc;

        $write = sprintf("\033[0G\033[2K $this->done/$this->total [%'={$bar_perc}s>%-{$left}s] - $this->perc%%", "", "");
        if ($this->done >= $this->total) $write .= "\n";
        fwrite(STDERR, $write);
    }
}