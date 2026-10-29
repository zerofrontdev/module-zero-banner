<?php
/**
 * Copyright © 2026 Roman Pasichnyk
 * SPDX-License-Identifier: OSL-3.0 OR AFL-3.0
 */

declare(strict_types=1);

namespace Zero\Banner\Setup\Patch\Data;

use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Zero\Banner\Setup\HomepageWidget;

/**
 * A fresh store's home page hero: the "Homepage hero" banner with one
 * slide about Zero, placed on the home page (until 1.3.3 also a Vilmate and a
 * Fixstacks slide: partners are on zerofront.dev/partners now). Only when the store has no banners yet; a store with banners
 * (ours, or one that made its own) is left alone. The slides are ordinary
 * slides afterwards: edit or delete them in Content > Banners.
 */
class CreateHomepageHero implements DataPatchInterface
{
    private const MEDIA_DIR = 'zero_banner/zero';
    private const IMAGES = ['zero-hero-desktop.jpg', 'zero-hero-mobile.jpg'];

    public function __construct(
        private readonly ModuleDataSetupInterface $moduleDataSetup,
        private readonly Filesystem $filesystem,
        private readonly HomepageWidget $homepageWidget
    ) {
    }

    public function apply(): void
    {
        $connection = $this->moduleDataSetup->getConnection();
        $banners = $this->moduleDataSetup->getTable('zero_banner_entity');
        if ($connection->fetchOne($connection->select()->from($banners, ['COUNT(*)']))) {
            return;
        }
        $connection->insert($banners, [
            'name' => 'Homepage hero',
            'identifier' => MigrateToBannersWithSlides::IDENTIFIER,
            'is_active' => 1,
            'aspect_ratio' => '5:2',
            'aspect_ratio_mobile' => '4:5',
            'width_mode' => 'contained',
            'is_above_fold' => 1,
            'show_arrows' => 1,
            'show_dots' => 1,
            'show_numbers' => 1,
            'autoplay' => 0,
            'autoplay_interval' => 6,
        ]);
        $bannerId = (int) $connection->lastInsertId($banners);
        $connection->insert($this->moduleDataSetup->getTable('zero_banner_entity_store'), ['banner_id' => $bannerId, 'store_id' => 0]);

        $media = $this->filesystem->getDirectoryWrite(DirectoryList::MEDIA);
        $media->create(self::MEDIA_DIR);
        foreach (self::IMAGES as $image) {
            $media->writeFile(self::MEDIA_DIR . '/' . $image, (string) file_get_contents(dirname(__DIR__, 2) . '/fixtures/zero/' . $image));
        }
        $connection->insert($this->moduleDataSetup->getTable('zero_banner_slide'), [
            'banner_id' => $bannerId,
            'is_active' => 1,
            'position' => 0,
            'image_fit' => 'cover',
            'image_position' => 'center',
            'content_position' => 'left',
            'eyebrow' => 'Zero',
            'heading' => 'A storefront built on the web platform.',
            'text' => 'Server-rendered HTML, HTMX and Tailwind CSS. No RequireJS, jQuery or Knockout: fast pages, a strict CSP, and a one-page checkout.',
            'button_text' => 'About Zero',
            'image' => '/' . self::MEDIA_DIR . '/' . self::IMAGES[0],
            'mobile_image' => '/' . self::MEDIA_DIR . '/' . self::IMAGES[1],
            'alt' => 'Zero',
            'link_url' => 'https://zerofront.dev',
        ]);

        $this->homepageWidget->place($bannerId);
    }

    public static function getDependencies(): array
    {
        return [MigrateToBannersWithSlides::class, AddVilmatePartnerSlide::class];
    }

    public function getAliases(): array
    {
        return [];
    }
}
