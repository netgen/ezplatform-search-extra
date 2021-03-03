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

    protected $searchHandler;
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
            PublishVersionEvent::class => 'onPublishVersion',
        ];
    }

    public function onPublishVersion(PublishVersionEvent $event): void
    {
        $this->handleEvent($event->getContent()->id);
    }

    public function onDeleteContent(DeleteContentEvent $event): void
    {
        $this->handleEvent($event->getContentInfo()->id);
    }

    public function onDeleteTranslation(DeleteTranslationEvent $event): void
    {
        $this->handleEvent($event->getContentInfo()->id);
    }

    public function onDeleteLocation(DeleteLocationEvent $event): void
    {
        $this->handleEvent($event->getLocation()->contentId);
    }

    public function onHideLocation(HideLocationEvent $event): void
    {
        $this->handleEvent($event->getLocation()->contentId);
    }

    public function onUnhideLocation(UnhideLocationEvent $event): void
    {
        $this->handleEvent($event->getRevealedLocation()->contentId);
    }

    public function onTrash(TrashEvent $event): void
    {
        $this->handleEvent($event->getLocation()->contentId);
    }

    public function onRecover(RecoverEvent $event): void
    {
        $this->handleEvent($event->getLocation()->contentId);
    }

    private function handleEvent(int $contentId): void
    {
        $contentHandler = $this->persistenceHandler->contentHandler();
        $contentInfo = $contentHandler->loadContentInfo($contentId);
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
