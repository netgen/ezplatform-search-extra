<?php

namespace Netgen\EzPlatformSearchExtra\Tests\Integration\Implementation\Common\Slot;

use eZ\Publish\Core\SignalSlot\Signal;
use eZ\Publish\Core\Search\Common\Slot;

class TestSubdocumentPublishVersion extends Slot
{
    /**
     * Receive the given $signal and react on it.
     *
     * @param \eZ\Publish\Core\SignalSlot\Signal $signal
     *
     * @throws \eZ\Publish\API\Repository\Exceptions\NotFoundException
     */
    public function receive(Signal $signal)
    {
        if (!$signal instanceof Signal\ContentService\PublishVersionSignal) {
            return;
        }

        $contentHandler = $this->persistenceHandler->contentHandler();
        $content = $contentHandler->load($signal->contentId, $signal->versionNo);
        $contentInfo = $content->versionInfo->contentInfo;
        $contentType = $this->persistenceHandler->contentTypeHandler()->load($contentInfo->contentTypeId);

        if ($contentType->identifier !== 'user') {
            return;
        }

        $parentLocation = $this->persistenceHandler->locationHandler()->load($contentInfo->mainLocationId);
        $parentContentInfo = $contentHandler->loadContentInfo($parentLocation->contentId);
        $parentContentType = $this->persistenceHandler->contentTypeHandler()->load($parentContentInfo->contentTypeId);

        if ($parentContentType->identifier !== 'user_group') {
            return;
        }

        $this->searchHandler->indexContent(
            $contentHandler->load($parentContentInfo->id, $parentContentInfo->currentVersionNo)
        );
    }
}
