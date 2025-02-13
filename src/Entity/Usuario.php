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

	#[ORM\Column(type:'string', name:'password')]
    private $password;

	#[ORM\Column(type:'string', name:'nombre')]
    private $nombre;

	#[ORM\Column(type:'string', name:'apellido')]
    private $apellido;

	#[ORM\Column(type:'datetime', name:'fecha_registro')]
    private $fecha_registro;

	#[ORM\Column(type:'integer', name:'rol')]
    private $rol;

    #[ORM\Column(type:'date', name:'fecha_nacimiento', nullable:true)]
    private $fecha_nacimiento;

    #[ORM\Column(type:'string', name:'localidad', nullable:true)]
    private $localidad;

    #[ORM\Column(type:'text', name:'biografia', nullable:true)]
    private $biografia;

    #[ORM\Column(type:'string', name:'activacion_token', nullable:true)]
    private $activacion_token;

    #[ORM\Column(type:'boolean', name:'verificado')]
    private $verificado = false;

    #[ORM\OneToMany(targetEntity:'Post', mappedBy:'usuario')]
    #[ORM\OrderBy(['fecha_publicacion' => 'DESC'])]
	private $posts;

    // public function __construct() {
    //     $this->posts = new ArrayCollection();
    // }
    
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
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword($password)
    {
        $this->password = $password;
    }

    public function getFechaNacimiento(): ?\DateTimeInterface
    {
        return $this->fecha_nacimiento;
    }

    public function setFechaNacimiento(?\DateTimeInterface $fecha_nacimiento): self
    {
        $this->fecha_nacimiento = $fecha_nacimiento;
        return $this;
    }

    public function getLocalidad()
    {
        return $this->localidad;
    }

    public function setLocalidad(?string $localidad)
    {
        $this->localidad = $localidad;
    }

    public function getBiografia()
    {
        return $this->biografia;
    }

    public function setBiografia(?string $biografia)
    {
        $this->biografia = $biografia;
    }

    public function getActivacion_token()
    {
        return $this->activacion_token;
    }

    public function setActivacion_token($activacion_token)
    {
        $this->activacion_token = $activacion_token;
    }

    public function isVerificado(): bool
    {
        return $this->verificado;
    }

    public function setVerificado($verificado)
    {
        $this->verificado = $verificado;
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

    public function __construct($email = null, $password = null, $nombre = null, $apellido = null, ?\DateTime $fecha_registro = null, $rol = null, ?\DateTime $fecha_nacimiento = null,
                                $localidad = null, $biografia = null, $activacion_token = null, $verificado = false) 
    {
        $this->email = $email;
        $this->password = $password;
        $this->nombre = $nombre;
        $this->apellido = $apellido;
        $this->fecha_registro = $fecha_registro ?? new \DateTime();
        $this->rol = $rol;
        $this->fecha_nacimiento = $fecha_nacimiento;
        $this->localidad = $localidad;
        $this->biografia = $biografia;
        $this->activacion_token = $activacion_token;
        $this->verificado = $verificado;
        $this->posts = new ArrayCollection();
    }
    
}