<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\Banner\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Takes out the Vilmate partner slides that AddVilmatePartnerSlide (the demo)
 * and CreateHomepageHero (a fresh store, until 1.3.3) put into homepage
 * heroes: Zero's partners are listed on zerofront.dev/partners instead.
 */
class RemoveVilmatePartnerSlides implements DataPatchInterface
{
    public const LINK = 'https://vilmate.com/';

    public function __construct(private readonly ModuleDataSetupInterface $moduleDataSetup)
    {
    }

    public function apply(): self
    {
        $this->moduleDataSetup->getConnection()->delete(
            $this->moduleDataSetup->getTable('zero_banner_slide'),
            ['link_url = ?' => self::LINK]
        );

        return $this;
    }

    public static function getDependencies(): array
    {
        return [AddVilmatePartnerSlide::class, CreateHomepageHero::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
