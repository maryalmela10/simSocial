<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

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
    private $fecha_publicacion;

    #[ORM\ManyToOne(targetEntity: 'Usuario', inversedBy: "posts")]
    #[ORM\JoinColumn(name:'usuario_id', referencedColumnName:'id')]
    private $usuario;

    #[ORM\OneToMany(targetEntity: 'Comentario', mappedBy: 'post')]
    private $comentarios;

    #[ORM\OneToMany(targetEntity: 'Reaccion', mappedBy: 'post', cascade: ['remove'])]
    private $reacciones;

    #[ORM\Column(type:'integer', name:'likes', options:["default" => 0])]
    private int $likes = 0;

    #[ORM\Column(type:'integer', name:'dislikes', options:["default" => 0])]
    private int $dislikes = 0;

    public function __construct(){
        $this->comentarios = new ArrayCollection();
        $this->reacciones = new ArrayCollection();
    }

    public function getComentarios(){
        return $this->comentarios;
    }

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

    public function getReacciones()
    {
        return $this->reacciones;
    }

    public function contarReacciones(string $tipo): int
    {
        return $this->reacciones->filter(fn($r) => $r->getTipo() === $tipo)->count();
    }    
    public function getLikes(): int {
        return $this->likes;
    }
    
    public function getDislikes(): int {
        return $this->dislikes;
    }
    
    public function addLike(): void {
        $this->likes++;
    }
    
    public function addDislike(): void {
        $this->dislikes++;
    }
}
