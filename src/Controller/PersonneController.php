<?php

namespace App\Controller;

use App\Entity\Personne;
use App\Repository\PersonneRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\SerializerInterface;

/**
 * Class PersonneController
 * @package App\Controller
 */
class PersonneController extends AbstractController
{
    /**
     * @Route("/api/personne/{id}", name="get_person_show", methods={"GET"})
     */
    public function show($id, PersonneRepository $personneRepository)
    {

        // recuperer une personne avec son id
        $personne = $personneRepository->find($id);

        // if person not found give new response with message
        if (!$personne) {
            return $this->json([
                "message" => "personne avec id: ${id} not found"
            ], Response::HTTP_NOT_FOUND);
        }

        // is ok person exist give a response ok
        return $this->json($personne, Response::HTTP_OK);
    }

    /**
     * @Route("/api/personne", name="post_person_create", methods={"POST"})
     */
    public function create(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        // deserialisation de contenu envoyer par utilisateur object json ===> vers object php
        $content = $request->getContent();
        $personne = $serializer->deserialize($content, Personne::class, 'json');

        // enregister l'objet php dans BD
        $entityManager->persist($personne);
        $entityManager->flush();

        // envoyer la reponse json pour utilisateur
        return $this->json(
            $personne,
            Response::HTTP_CREATED // Response::HTTP_CREATED ====> 201
        );
    }

    /**
     * @Route("/api/personne", name="get_person_list", methods={"GET"})
     */
    public function list(PersonneRepository $personneRepository)
    {
        $personnes = $personneRepository->findAll();
        return $this->json($personnes);
    }

    /**
     * @Route("/api/personne/{id}", name="put_person_update", methods={"PUT"})
     */
    public function update($id,
                           Request $request, //recuperer le contenu envoyer par utilisateur
                           PersonneRepository $personneRepository,  // repository pour recuperer => lecture en base donnee
                           SerializerInterface $serializer, // service de deserialisation et serialisation
                           EntityManagerInterface $entityManager // ecrirure ou suprimer en base donnee
    )
    {

        // recuperer la personne sur la base donner avec id
        $personne = $personneRepository->find($id);

        // mettre a jour la personne avec contenu envoyer
        $serializer->deserialize(
            $request->getContent(),
            Personne::class,
            'json',
            [AbstractNormalizer::OBJECT_TO_POPULATE => $personne]);

        // enregistrer le modifs dans la base donnees
        $entityManager->flush();
        return $this->json($personne);
    }

    /**
     * @Route("/api/personne/{id}", name="delete_person_remove", methods={"DELETE"})
     */
    public function remove($id, EntityManagerInterface $entityManager, PersonneRepository $personneRepository)
    {

        // recuperer la personne a supprimer
        $personne = $personneRepository->find($id);


        if (!$personne) {
            return $this->json([
                "message" => "personne avec id: ${id} not found"
            ], Response::HTTP_NOT_FOUND);
        }

        // supprime la personne de la base de donner
        $entityManager->remove($personne);
        $entityManager->flush();

        return $this->json("", Response::HTTP_NO_CONTENT);
    }

}
