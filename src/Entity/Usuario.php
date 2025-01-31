<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\Common\Collections\ArrayCollection;


#[ORM\Entity]
#[ORM\Table(name: "usuarios")]
class Usuario implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\Column(type:'integer', name:'id')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type:'string', name:'email')]
    private $email;

	#[ORM\Column(type:'string', name:'clave')]
    private $clave;

	#[ORM\Column(type:'string', name:'nombre')]
    private $nombre;

	#[ORM\Column(type:'string', name:'apellido')]
    private $apellido;

	#[ORM\Column(type:'datetime', name:'fecha_registro')]
    private $fecha_registro;

	#[ORM\Column(type:'integer', name:'rol')]
    private $rol;

    #[ORM\OneToMany(targetEntity:'Posts', mappedBy:'usuarios')]
	private $posts;

    public function __construct() {
        $this->posts = new ArrayCollection();
    }
    
    public function getPosts() {
        return $this->posts;    
    }

    public function getRol(){
        return $this->rol;
    }

    public function setRol($rol){
        $this->rol = $rol;
    }

    public function getId(){
        return $this->id;
    }
    public function getEmail(){
        return $this->email;
    }
    public function setEmail($email){
        $this->email = $email;
    }
    public function getClave(){
        return $this->clave;
    }
    public function setClave($clave){
        $this->clave = $clave;
    }
    public function getNombre(){
        return $this->nombre;
    }
    public function setNombre($nombre){
        $this->nombre = $nombre;
    }
	public function getApellido(){
        return $this->apellido;
    }

    public function setApellido($apellido) {
        $this->apellido = $apellido;
    }

    public function getFecha_registro(){
        return $this->fecha_registro;
    }

    public function setFecha_registro($fecha_registro){
        $this->fecha_registro = $fecha_registro;
    }

	public function getRoles(): array
{
    $roles = ['ROLE_USER'];
    
    // Comprueba si el usuario también es admin
    if ($this->rol == "ROLE_ADMIN") { 
        $roles[] = 'ROLE_ADMIN';
    }

    return array_unique($roles);
}

    public function getPassword(): string
    {
        return $this->getClave();
    }


    public function getUserIdentifier(): string
    {
        return $this->getEmail();
    }

    public function getSalt(): ?string
    {
        return null;
    }
	
    public function eraseCredentials(): void
    {

    }
    
}