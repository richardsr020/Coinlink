<?php
// src/Controller/ChatController.php

namespace App\Controller;

use App\Service\ChatService;
use App\Entity\User;
use App\Entity\Message;
use App\Form\MessageType;
use App\Form\SearchUserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ChatController extends AbstractController
{
    private $chatService;

    // Injection du service de chat dans le contrôleur
    public function __construct(ChatService $chatService)
    {
        $this->chatService = $chatService;
    }

    
    #[Route('/chat/conversation/{userId}', name: 'chat_conversation', methods: ['GET', 'POST'])]
    public function conversation(Request $request, int $userId, EntityManagerInterface $em): Response
    {
        // Récupère l'utilisateur actuellement connecté
        $currentUser = $this->getUser();
        //
        $conversationId_1 = $currentUser->getId() . $userId;
        $conversationId_2 = $userId. $currentUser->getId();

        // Récupère les messages de la conversation
        $messages = $this->chatService->getMessagesByConversationId($conversationId_1, $conversationId_2);
        $messageArray = [];

        foreach ($messages as $message) {
            $messageArray[] = [
                'id' => $message->getId(),
                'sender' => $message->getSender()->getName(),
                'receiver' => $message->getReceiver()->getId(),
                'content' => $message->getContent(),
                'createdAt' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
                'thumbnail'=>$message->getThumbnail(),
            ];
        }

        // Créer le formulaire de message
        $message = new Message();
        //dd($message);
        $form = $this->createForm(MessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $message->setSender($currentUser);
            $message->setReceiver($em->getRepository(User::class)->find($userId));
            $message->setCreatedAt(new \DateTimeImmutable());
            $message->setConversationId($conversationId_1);
            $message->setSeen(false);

            // Récupère le contenu du message et l'attachement
            $content = $message->getContent();
            $attachment = $form->get('thumbnail')->getData();

            // Empêche l'envoi d'une image seule sans message
            if (empty($content) && !$attachment) {
                $this->addFlash('error', 'Vous ne pouvez pas envoyer une image seule sans message.');
            } else {
                if ($attachment) {
                    // Valider l'attachement (seulement les images)
                    $mimeType = $attachment->getMimeType();
                    if (strpos($mimeType, 'image') !== 0) {
                        $this->addFlash('error', 'Seules les images sont autorisées comme pièces jointes.');
                    } else {
                        // Sauvegarde l'attachement s'il est valide
                        $filename = uniqid() . '.' . $attachment->guessExtension();
                        $attachment->move(
                            $this->getParameter('message_thumbnail_directory'), // Assurez-vous que ce paramètre est défini dans votre config
                            $filename
                        );
                        $message->setThumbnail($filename);
                    }
                }
                // Enregistre le message dans la base de données
                $this->chatService->saveMessage($message);
                $this->addFlash('success', 'Message envoyé avec succès.');
                return $this->redirectToRoute('chat_conversation', ['userId' => $userId]);
            }
        }
        return $this->render('chat/conversation.html.twig', [
            'messages' => $messageArray,
            'conversationId' => $conversationId_1,
            'form' => $form->createView(),
        ]);
    }


    private function getLastMessageByConversationId(int $currentUserId): array
    {
        // Récupère le dernier message d'une conversation
        $lastMessage = $this->chatService->getLastMessagesByUser($currentUserId);
        //dd($lastMessage);
        if ($lastMessage) {

            return $lastMessage;
            // return [
            //     'recentChat' => [
            //         'id' => $lastMessage[1]->getId(),
            //         'sender' => $lastMessage[1]->getSender(),
            //         'receiver' => $lastMessage[1]->getReceiver(),
            //         'content' => $lastMessage[1]->getContent(),
            //         'createdAt' => $lastMessage[1]->getCreatedAt()->format('Y-m-d H:i:s'),
            //     ],
            // ];
        }

        // Retourne une réponse standard pour non trouvé
        return $lastMessage;
    }
    /**
     * 
     */
    #[Route('/chat', name: 'app_chat')]
    public function getUserByEmail(Request $request): Response
    {
        // Crée le formulaire de recherche d'utilisateur
        $form = $this->createForm(SearchUserType::class);
        $form->handleRequest($request);

        $error = null; // Variable pour stocker le message d'erreur
        //recuperer l'utilisateur actuellement connecté
        $currentUser = $this->getUser();
        $currentUserId = $currentUser->getId();

        // Vérifie si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            // Récupère les données du formulaire
            $data = $form->getData();

            // Récupère l'utilisateur par son adresse e-mail
            $user = $this->chatService->getUserByEmail($data->getEmail());

            $recentChat = $this->getLastMessageByConversationId($currentUserId);
            

            if ($user) {
                // Si l'utilisateur est trouvé, affiche ses informations
                return $this->render('chat/chatbox.html.twig', [
                    'user' => [
                        'id' => $user->getId(),
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                    ],
                    'form' => $form->createView(),
                    'recentChat' => $recentChat,
                ]);
            } else {
                // Si l'utilisateur n'est pas trouvé, définit le message d'erreur
                $error = "Aucun utilisateur trouvé avec cet e-mail.";
            }
        }
        $recentChat = $this->getLastMessageByConversationId($currentUserId);
        //dd($recentChat);
        // Retourne le formulaire et le message d'erreur s'il y en a un
        return $this->render('chat/chatbox.html.twig', [
            'form' => $form->createView(),
            'error' => $error,
            'user'=> '',
            'recentChat' => $recentChat,
        ]);
    }


    #[Route('/chat/send/{receiverUser}', name: 'chat_save_message', methods: ['POST'])]
    public function saveMessage(Request $request): Response
    {
        // Récupère les données du corps de la requête
        $data = $request->request->all();
        $user = $this->getUser();
        $userId = $user->getId();
        //Ici, vous devrez ajouter la logique pour créer un message
    
        $message = new Message();
        $message->setContent($data['content']);
        $message->setSender($user); // Assurez-vous que l'utilisateur est authentifié
        $message->setReceiver($receiverUser);
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setConversationId = $userId.$receiverUser;
        $this->chatService->saveMessage($message);

        // Retournez une réponse appropriée après la sauvegarde du message
        return $this->render('chat/message_sent.html.twig', [
            'status' => 'Message sent',
        ]);
    }
}
