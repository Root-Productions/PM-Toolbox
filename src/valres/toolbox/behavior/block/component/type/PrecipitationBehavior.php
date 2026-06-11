<?php

declare(strict_types=1);

namespace valres\toolbox\behavior\block\component\type;

enum PrecipitationBehavior: string {
    case OBSTRUCT_RAIN_ACCUMULATE_SNOW = "obstruct_rain_accumulate_snow";
    case OBSTRUCT_RAIN = "obstruct_rain";
    case NONE = "none";
}
