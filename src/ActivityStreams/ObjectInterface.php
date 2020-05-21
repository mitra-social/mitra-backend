<?php

declare(strict_types=1);

namespace Mitra\ActivityStreams;

interface ObjectInterface
{
    public function getAttachment();
    public function getAttributedTo();
    public function getAudience();
    public function getContent();
    public function getContext();
    public function getName();
    public function getEndTime();
    public function getGenerator();
    public function getIcon();
    public function getImage();
    public function getInReplyTo();
    public function getLocation();
    public function getPreview();
    public function getPublished();
    public function getReplies();
    public function getStartTime();
    public function getSummary();
    public function getTag();
    public function getUpdated();
    public function getUrl();
    public function getTo();
    public function getBto();
    public function getCc();
    public function getBcc();
    public function getMediaType();
    public function getDuration();
}
