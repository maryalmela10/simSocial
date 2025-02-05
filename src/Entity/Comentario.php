<?php
namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
#[ORM\Entity]
#[ORM\Table(name: "comentarios")]
class Comentario
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer',name:'id')]
    private int $id;

    #[ORM\Column(type:'text',name:'contenido')]
    private string $contenido;

    #[ORM\Column(type: 'datetime', name: 'fecha_comentario')]
    private $fecha_comentario;

    #[ORM\ManyToOne(targetEntity: 'Post', inversedBy: "comentarios")]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id')]
    private $post;

    #[ORM\ManyToOne(targetEntity: 'Usuario', inversedBy: "comentarios")]
    #[ORM\JoinColumn(name: 'usuario_id', referencedColumnName: 'id')]
    private $usuario;

    public function getId()
    {
        return $this->id;
    }

    public function getContenido()
    {
        return $this->contenido;
    }

    public function setContenido(string $contenido)
    {
        $this->contenido = $contenido;
    }

    public function getFechaComentario()
    {
        return $this->fecha_comentario;
    }

    public function setFechaComentario($fechaComentario)
    {
        $this->fecha_comentario = $fechaComentario;
    }

    public function getPost()
    {
        return $this->post;
    }

    public function setPost($post)
    {
        $this->post = $post;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario($usuario)
    {
        $this->usuario = $usuario;
    }
}
