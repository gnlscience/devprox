<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\People;
use App\Service\PeopleService;
use Symfony\Component\HttpFoundation\Request;

class Test1Controller extends AbstractController
{
    private $peopleService;

    public function __construct(PeopleService $peopleService)
    {
        $this->peopleService = $peopleService;
    }

    /**
     * @Route("/test1", methods={"GET"})
     */
    public function index()
    {
	    return $this->render('test1.html.twig');
    }

    /**
     * @Route("/test1/results", methods={"GET"})
     */
    public function results()
    {
	    return $this->render('test1.results.html.twig', [ "people" => $this->peopleService->findAll()]);
    }

    /**
     * @Route("api/test1", methods={"POST"})
     */
    public function insert(Request $request)
    {
        $postData = json_decode($request->getContent(), true); 
        try{
            $person = new People(); 
            $person->setName($postData['name']);
            $person->setSurname($postData['surname']);
            $person->setIdNumber($postData['id_number']);
            $person->setDOB($postData['dob']);
            $this->peopleService->create($person);
            return $this->json(['message' => 'Success'], 200);
        }catch(\Exception $e){
            return $this->json(['message' => $e->getMessage()], 400);
        }
    }     
}
