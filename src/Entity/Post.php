<?php
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: "posts")]
class Post
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer', name:'id')]
    private int $id;

    #[ORM\Column(type:'text', name:'contenido')]
    private string $contenido;

    #[ORM\Column(type:'datetime', name:'fecha_publicacion')]
    private $fechaPublicacion;

    #[ORM\ManyToOne(targetEntity: 'Usuario', inversedBy: "posts")]

    #[ORM\JoinColumn(name:'usuario_id', referencedColumnName:'id')]
    private $usuario;

    public function getId()
    {
        return $this->id;
    }

    public function getContenido()
    {
        return $this->contenido;
    }

    public function getFecha_publicacion()
    {
        return $this->fecha_publicacion;
    }
	public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    }

    public function setFecha_publicacion($fecha_Publicacion)
    {
        return $this->fecha_publicacion = $fecha_Publicacion;
    }

}
