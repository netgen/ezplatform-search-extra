<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;

class TestChildUpdatesParent extends Slot
{
    const PARENT_CONTENT_TYPE_IDENTIFIER = 'extra_fields_test';

    const CHILD_CONTENT_TYPE_IDENTIFIER = 'extra_fields_test_comment';

    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function receive(Signal $signal)
    {
        if (
            !$signal instanceof Signal\ContentService\PublishVersionSignal
            && !$signal instanceof Signal\ContentService\DeleteContentSignal
            && !$signal instanceof Signal\LocationService\DeleteLocationSignal
            && !$signal instanceof Signal\LocationService\HideLocationSignal
            && !$signal instanceof Signal\LocationService\UnhideLocationSignal
            && !$signal instanceof Signal\TrashService\TrashSignal
            && !$signal instanceof Signal\TrashService\RecoverSignal
        ) {
            return;
        }

        $contentHandler = $this->persistenceHandler->contentHandler();
        $content = $contentHandler->load($signal->contentId, $signal->versionNo);
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
