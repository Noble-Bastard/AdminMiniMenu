<?php

namespace Menu;

use pocketmine\plugin\PluginBase;
use pocketmine\command\{Command, CommandSender};
use pocketmine\event\Listener;
use pocketmine\Player;
use pocketmine\Server;
use jojoe77777\FormAPI\SimpleForm;
use jojoe77777\FormAPI\CustomForm;

class Bastard extends PluginBase implements Listener {

   private static $online = [];

   public function onCommand(CommandSender $player, Command $command, string $label, array $args): bool
   {
      if ($command->getName() == "admmenu") {
         if (!$player instanceof Player) {
            $player->sendMessage("Пиши эту команду в игре!");
         } else {
            if ($player->isOp() or $player->hasPermission("adm.use")) {
               Bastard::AdmMenuMain($player);
            } else {
               $player->sendMessage("Нету прав, бро(");
            }
         }
      }
      return true;
   }

   public static function AdmMenuMain($player)
   {
      $form = new SimpleForm(function (Player $player, $data) {
        $result = $data;
            if ($result === null) {
               return true;
            }
            switch ($result) {
            case 0:
               Bastard::TellMessage($player);
               break;
            case 1:
               Bastard::TellBroadcast($player);
               break;
            case 2:
               Bastard::AdminList($player);
               break;
            case 3:
               Bastard::KickPlayer($player);
               break;
            }
         });
      $online = count(Server::getInstance()->getOnlinePlayers());

      $dir = opendir('players');
      $count = 0;
      while ($file = readdir($dir)) {
         if ($file == '.' || $file == '..' || is_dir('players' . $file)) {
            continue;
         }
         $count++;
      }
      /* можно конечно было и через scandir просканировать, но, там чет хз числа удваиваются, да и смысла нет использовать scandir если там овер-дохрена файлов) */

      $tps = Server::getInstance()->getTicksPerSecond();

      $form->setTitle("Админ-Панель");
      $form->setContent("Текущий онлайн: {$online}\nКоличество зарегестрированных игроков: {$count}\nТекущий TPS: {$tps}\n\n");
      $form->addButton("СООБЩЕНИЕ ИГРОКУ", 0, "textures/blocks/chain_command_block_conditional_mipmap");
      $form->addButton("СООБЩЕНИЕ НА ВЕСЬ СЕРВЕР", 0, "textures/map/map_background");
      $form->addButton("АДМИНЫ КОТОРЫЕ ОНЛАЙН", 0, "textures/items/map_filled");
      $form->addButton("КИК", 0, "textures/blocks/barrier");
      $form->sendToPlayer($player);
   }

   public static function KickPlayer($player)
   {
      $players = [];
      foreach (Server::getInstance()->getOnlinePlayers() as $p) {
         $players[] = $p->getName();
      }
     self::$online[$player->getName()] = $players;

      $form = new CustomForm(function (Player $player, array $data = null) {
         $result = $data;
         if ($result === null) {
            return true;
         }
         $index = $data[1];
         $predlog = $data[2];
         $playerName = self::$online[$player->getName()][$index];

         $player2 = Server::getInstance()->getPlayer($playerName);

         if ($player2->getName() == $player->getName()) {
            $player->sendMessage("Ты не можешь кикнуть себя!");
         } else {
            $player2->close("", "Тебя кикнул {$player->getName()} .\nПричина - {$predlog}.");;
         }
      });
      $form->setTitle("Кик-Меню");
      $form->addLabel("Кик игрока!");
      $form->addDropdown("Игрок",self::$online[$player->getName()]);
      $form->addInput("Причина", "Нехрен было мои алмазы воровать!");
      $form->sendToPlayer($player);
   }

   public static function TellMessage($player)
   {
      $players = [];
      foreach (Server::getInstance()->getOnlinePlayers() as $p) {
         $players[] = $p->getName();
      }
     self::$online[$player->getName()] = $players;
      $form = new CustomForm(function (Player $player, array $data = null) {
         $result = $data;
         if ($result === null) {
            return true;
         }
         $index = $data[1];
         $predlog = $data[2];
         $playerName = self::$online[$player->getName()][$index];

         $player2 = Server::getInstance()->getPlayer($playerName);

         if ($player2->getName() == $player->getName()) {
            $player->sendMessage("Ты не можешь отправить сообщение себе самому!");
         } else {
            $player2->sendMessage("{$player->getName()} Передал тебе сообщение: {$predlog}");
         }
      });
      $form->setTitle("Сообщение игроку");
      $form->addLabel("Побалтай с игроком в лс, но все же, советую использовать мессенджеры, вдруг админ уебок сольет вашу переписку)");
      $form->addDropdown("Игрок", self::$online[$player->getName()]);
      $form->addInput("Сообщение", "Дина, где алмазы?");
      $form->sendToPlayer($player);
   }

   public static function TellBroadcast($player)
   {
      $form = new CustomForm(function (Player $player, array $data = null) {
         $result = $data;
         if ($result === null) {
            return true;
         }
         $predlog = $data[1];
         $playername = $player->getName();
         Server::getInstance()->broadcastMessage("{$playername} Кричит: {$predlog}");
      });
      $form->setTitle("Сообщение всему серверу");
      $form->addLabel("Напиши всем что ты лоликонщик");
      $form->addInput("Сообщение", "Я люблю смотреть аниме");
      $form->sendToPlayer($player);
   }

   public static function AdminList($player)
   {
      $form = new SimpleForm(function (Player $player, $data) {
         $result = $data;
         if ($result === null) {
            return true;
         }
         switch ($result) {
            case 0;
               Bastard::AdmMenuMain($player);
               break;
         }
      });
      $online = "";
      $count = 0;
      foreach (Server::getInstance()->getOnlinePlayers() as $players) {
         if ($players->hasPermission("adm.use")) {
            $count++;
            $online .= $players->getDisplayName() . "§r, ";
         }
      }

      $form->setTitle("Админы в сети");
      $form->setContent("Админов в сети: {$count}\nНики- {$online}\n\n");
      $form->addbutton("Назад", 0);
      $form->sendToPlayer($player);
   }

}
