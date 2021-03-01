<?php

declare(strict_types=1);

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Common\EventSubscriber;

use eZ\Publish\API\Repository\Events\Content\DeleteContentEvent;
use eZ\Publish\API\Repository\Events\Content\DeleteTranslationEvent;
use eZ\Publish\API\Repository\Events\Content\PublishVersionEvent;
use eZ\Publish\API\Repository\Events\Location\DeleteLocationEvent;
use eZ\Publish\API\Repository\Events\Location\HideLocationEvent;
use eZ\Publish\API\Repository\Events\Location\UnhideLocationEvent;
use eZ\Publish\API\Repository\Events\Trash\RecoverEvent;
use eZ\Publish\API\Repository\Events\Trash\TrashEvent;
use eZ\Publish\SPI\Persistence\Handler as PersistenceHandler;
use eZ\Publish\SPI\Search\Handler as SearchHandler;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class TestChildUpdatesParent implements EventSubscriberInterface
{
    private const PARENT_CONTENT_TYPE_IDENTIFIER = 'extra_fields_test';
    private const CHILD_CONTENT_TYPE_IDENTIFIER = 'extra_fields_test_comment';
    /** @var \eZ\Publish\SPI\Search\Handler */
    protected $searchHandler;

    /** @var \eZ\Publish\SPI\Persistence\Handler */
    protected $persistenceHandler;

    public function __construct(
        SearchHandler $searchHandler,
        PersistenceHandler $persistenceHandler
    ) {
        $this->searchHandler = $searchHandler;
        $this->persistenceHandler = $persistenceHandler;
    }

    public static function getSubscribedEvents(): array
    {
        return [
            PublishVersionEvent::class => 'handlePublishVersionEvent',
            DeleteContentEvent::class => 'handleDeleteContentEvent',
            DeleteTranslationEvent::class => 'handleDeleteTranslationEvent',
            DeleteLocationEvent::class => 'handleDeleteLocationEvent',
            HideLocationEvent::class => 'handleHideLocationEvent',
            UnhideLocationEvent::class => 'handleUnhideLocationEvent',
            TrashEvent::class => 'handleTrashEvent',
            RecoverEvent::class => 'handleRecoverEvent',
        ];
    }

    public function handlePublishVersionEvent(PublishVersionEvent $event): void
    {
        $this->handleEvent(
            $event->getContent()->id,
            $event->getVersionInfo()->versionNo
        );
    }

    public function handleDeleteContentEvent(DeleteContentEvent $event): void
    {
        $this->handleEvent(
            $event->getContentInfo()->id,
            $event->getContentInfo()->currentVersionNo
        );
    }

    public function handleDeleteTranslationEvent(DeleteTranslationEvent $event): void
    {
        $this->handleEvent(
            $event->getContentInfo()->id,
            $event->getContentInfo()->currentVersionNo
        );
    }

    public function handleDeleteLocationEvent(DeleteLocationEvent $event): void
    {
        $this->handleEvent(
            $event->getLocation()->contentId,
            $event->getLocation()->contentInfo->currentVersionNo
        );
    }

    public function handleHideLocationEvent(HideLocationEvent $event): void
    {
        $this->handleEvent(
            $event->getLocation()->contentId,
            $event->getLocation()->contentInfo->currentVersionNo
        );
    }

    public function handleUnhideLocationEvent(UnhideLocationEvent $event): void
    {
        $this->handleEvent(
            $event->getRevealedLocation()->contentId,
            $event->getRevealedLocation()->contentInfo->currentVersionNo
        );
    }

    public function handleTrashEvent(TrashEvent $event): void
    {
        $this->handleEvent(
            $event->getLocation()->contentId,
            $event->getLocation()->contentInfo->currentVersionNo
        );
    }

    public function handleRecoverEvent(RecoverEvent $event): void
    {
        $this->handleEvent(
            $event->getLocation()->contentId,
            $event->getLocation()->contentInfo->currentVersionNo
        );
    }

    private function handleEvent(int $contentId, int $versionNo): void
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $content = $contentHandler->load($contentId, $versionNo);
        $contentInfo = $content->versionInfo->contentInfo;
        $contentType = $this->persistenceHandler->contentTypeHandler()->load($contentInfo->contentTypeId);

        if ($contentType->identifier !== self::CHILD_CONTENT_TYPE_IDENTIFIER) {
            return;
        }

        $location = $this->persistenceHandler->locationHandler()->load($contentInfo->mainLocationId);
        $parentLocation = $this->persistenceHandler->locationHandler()->load($location->parentId);
        $parentContentInfo = $contentHandler->loadContentInfo($parentLocation->contentId);
        $parentContentType = $this->persistenceHandler->contentTypeHandler()->load($parentContentInfo->contentTypeId);

        if ($parentContentType->identifier !== self::PARENT_CONTENT_TYPE_IDENTIFIER) {
            return;
        }

        $this->searchHandler->indexContent(
            $contentHandler->load($parentContentInfo->id, $parentContentInfo->currentVersionNo)
        );
    }
}
