<?php

namespace Com\KeltieCochrane\Juicer;

use Com\KeltieCochrane\Juicer\Feed\Model as FeedModel;

class Factory
{
  /**
   * Returns a feed Model for easier access
   * @return  \\Com\KeltieCochrane\Juicer\Feed\Model
   */
  public function feed (string $slug)
  {
    return new FeedModel($slug);
  }
}
