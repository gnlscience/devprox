<?php
namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Entity\People; 
use function Functional\first;

class PeopleService
{
    private $em;
    private $firstNames = ['Tajuana', 'Naoma','Marni','Tobi','Coretta','Madeline','Waneta','Onie',
        'Dorian','Harold','Elyse','Minh','Yong','Machelle','Marcelo','Lelah','Freida','Amiee',
        'Harland','Winifred'];
    private $surNames = ['Goodwin','Hunt','Mcgrath','Gregory','Horton','Thompson','Pierce',
        'Kerr','Zhang','Gonzales','Spears','Ashley','Mcbride','Luna','Simmons','Collier'
        ,'Farmer','Hughes','Ellis','Copeland'];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function createList($total){
        $list = [];
        $minAge = 20;
        while(count($list) < $total){
            $dob = $this->randomDob($minAge);
            foreach($this->firstNames as $firstName){
                foreach($this->surNames as $surName){
                    $list[] = $this->createListPerson($firstName, $surName, $dob);
                }
            }
            $minAge++;
        } 
        return $list;
    }

    private function createListPerson($firstName, $surName, $dob){
        $initial = substr($firstName, 0, 1) . substr($surName, 0, 1);
            $age = floor((time() - strtotime($dob)) / 31556926);
        return [
            "id" => md5('"' . $firstName . '","' . $surName . '","' . $initial . '","' . $dob . '","' . $age),
            "firstName" => $firstName,
            "lastName" => $surName,
            "initials" => substr($firstName, 0, 1) . substr($surName, 0, 1),
            "dob" => $dob,
            "age" => $this->getAge($dob)        
        ]; 
    }
    private function getAge($date) {
        return intval(date('Y', time() - strtotime($date))) - 1970;
    }
    private function isListedPerson($list, $person){
        return first($list, function($item) use($person){ $item == $person; });
    }

    private function randomDob($minAge) {
        //$max = strtotime($maxAge . ' years ago');
        
        $min = strtotime($minAge . ' days ago');
        //die($min. " ". $max) ;

        //$rand_time = mt_rand($max, $min);
        //return date('m-d-Y', $rand_time);

        return date('m-d-Y', $min);
    }

    public function create(People $person){
        $this->hasValidId($person); 
        $this->hasValidDOB($person); 
        $this->hasMatchingDOBAndId($person); 
        $this->isAlphanumeric($person->getName());
        $this->isAlphanumeric($person->getName());
        $this->isDuplicateId($person);
        $this->em->persist($person);
        $this->em->flush();
    }

    public function findAll(){
        $repository = $this->em->getRepository(People::class);
        return $repository->findAll();
    }

    private function isDuplicateId(People $person){
        $repository = $this->em->getRepository(People::class);
        $person = $repository->findOneBy(['id_number' => $person->getIdNumber()]);
        if($person){
            throw new \InvalidArgumentException('ID Already taken!');
        }
    }

    private function hasValidId(People $person)
    {
        if(!strlen($person->getIdNumber()) === 13){
            throw new \InvalidArgumentException('Invalid Id!');
        }
    }

    private function hasValidDOB(People $person)
    {
        $validationDate = \DateTime::createFromFormat('d/m/Y', $person->getDob());
        if(!($validationDate && $validationDate->format('d/m/Y') == $person->getDob())){
            throw new \InvalidArgumentException('Invalid Dob Format!');
        }
    }

    private function hasMatchingDOBAndId(People $person)
    {
        if(! 
            ((substr($person->getIdNumber(), 0, 2) === substr($person->getDob(), 8, 2))
            && (substr($person->getIdNumber(), 2, 2) === substr($person->getDob(), 3, 2))
            && (substr($person->getIdNumber(), 4, 2) === substr($person->getDob(), 0, 2)))
        ){
            throw new \InvalidArgumentException('Dob not Matching!');
        }
    }

    private function isAlphanumeric(string $text){
        if(! (preg_match("/^[a-zA-Z'-]+$/", $text))){
            throw new \InvalidArgumentException('Check name or Surname!');
        }
    }
}