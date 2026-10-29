<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\Banner\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Added a Vilmate partner slide to the demo's homepage heroes until 1.3.3.
 * Does nothing now (partners are on zerofront.dev/partners); kept so the
 * patch history stays as it was. RemoveVilmatePartnerSlides takes the slides
 * out of the stores that got them.
 */
class AddVilmatePartnerSlide implements DataPatchInterface
{
    public function apply(): void
    {
    }

    public static function getDependencies(): array
    {
        return [MigrateToBannersWithSlides::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
