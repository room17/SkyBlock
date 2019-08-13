<?php
/**
 *  _____    ____    ____   __  __  __  ______
 * |  __ \  / __ \  / __ \ |  \/  |/_ ||____  |
 * | |__) || |  | || |  | || \  / | | |    / /
 * |  _  / | |  | || |  | || |\/| | | |   / /
 * | | \ \ | |__| || |__| || |  | | | |  / /
 * |_|  \_\ \____/  \____/ |_|  |_| |_| /_/
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 */

declare(strict_types=1);

namespace room17\SkyBlock\session;


use pocketmine\Player;
use room17\SkyBlock\island\Island;
use room17\SkyBlock\utils\MessageContainer;

class Session extends BaseSession {
    
    /** @var Player */
    private $player;
    
    /** @var null|Island */
    private $island = null;
    
    /** @var string|null */
    private $lastInvitation = null;
    
    /** @var array */
    private $invitations = [];
    
    /**
     * Session constructor.
     * @param SessionManager $manager
     * @param Player $player
     */
    public function __construct(SessionManager $manager, Player $player) {
        $this->player = $player;
        parent::__construct($manager, $player->getLowerCaseName());
    }
    
    /**
     * @return Player
     */
    public function getPlayer(): Player {
        return $this->player;
    }
    
    /**
     * @return null|Island
     */
    public function getIsland(): ?Island {
        return $this->island;
    }
    
    /**
     * @return bool
     */
    public function hasIsland(): bool {
        return $this->island != null;
    }
    
    /**
     * @return OfflineSession
     */
    public function getOffline(): OfflineSession {
        return new OfflineSession($this->manager, $this->username);
    }
    
    /**
     * @return array
     */
    public function getInvitations(): array {
        return $this->invitations;
    }
    
    /**
     * @param string $senderName
     * @return null|Island
     */
    public function getInvitation(string $senderName): ?Island {
        return $this->invitations[$senderName] ?? null;
    }
    
    /**
     * @return null|string
     */
    public function getLastInvitation(): ?string {
        return $this->lastInvitation;
    }
    
    /**
     * @return bool
     */
    public function hasLastInvitation(): bool {
        return $this->lastInvitation != null;
    }
    
    /**
     * @param null|string $identifier
     */
    public function setIslandId(?string $identifier): void {
        parent::setIslandId($identifier);
        if($identifier != null) {
            $this->provider->loadIsland($identifier);
            $this->island = $this->manager->getPlugin()->getIslandManager()->getIsland($identifier);
        }
    }
    
    /**
     * @param null|Island $island
     */
    public function setIsland(?Island $island): void {
        $lastIsland = $this->island;
        $this->island = $island;
        $this->islandId = ($island != null) ? $island->getIdentifier() : null;
        if($island != null) {
            $island->addMember($this->getOffline());
        }
        if($lastIsland != null) {
            $lastIsland->updateMembers();
        }
        $this->save();
    }
    
    /**
     * @param array $invitations
     */
    public function setInvitations(array $invitations): void {
        $this->invitations = $invitations;
    }
    
    /**
     * @param string $senderName
     * @param Island $island
     */
    public function addInvitation(string $senderName, Island $island): void {
        $this->invitations[$senderName] = $island;
        $this->lastInvitation = $senderName;
    }
    
    /**
     * @param string $senderName
     */
    public function removeInvitation(string $senderName): void {
        if(isset($this->invitations[$senderName])) {
            unset($this->invitations[$senderName]);
        }
    }
    
    /**
     * @param null|string $senderName
     */
    public function setLastInvitation(?string $senderName): void {
        $this->lastInvitation = $senderName;
    }

    /**
     * @param MessageContainer $container
     * @return string
     */
    public function getMessage(MessageContainer $container): string {
        return $this->manager->getPlugin()->getSettings()->getMessage($$container);
    }

    /**
     * @param MessageContainer $container
     */
    public function sendTranslatedMessage(MessageContainer $container): void {
        $this->player->sendMessage($this->getMessage($container));
    }

    /**
     * @param MessageContainer $container
     */
    public function sendTranslatedPopup(MessageContainer $container): void {
        $this->player->sendPopup($this->getMessage($container));
    }

    /**
     * @param MessageContainer $container
     */
    public function sendTranslatedTip(MessageContainer $container): void {
        $this->player->sendTip($this->getMessage($container));
    }
    
}