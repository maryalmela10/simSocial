<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: "reacciones")]
#[ORM\UniqueConstraint(name: "usuario_post_tipo", columns: ["usuario_id", "post_id", "tipo"])]
class Reaccion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer", name: "id")]
    private int $id;

    #[ORM\ManyToOne(targetEntity: "Usuario")]
    #[ORM\JoinColumn(name: "usuario_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Usuario $usuario;

    #[ORM\ManyToOne(targetEntity: "Post", inversedBy: "reacciones")]
    #[ORM\JoinColumn(name: "post_id", referencedColumnName: "id", nullable: false, onDelete: "CASCADE")]
    private Post $post;

    #[ORM\Column(type: "string", length: 15, name: "tipo")]
    private string $tipo;

    public function getId()
    {
        return $this->id;
    }

    public function getUsuario()
    {
        return $this->usuario;
    }

    public function setUsuario(Usuario $usuario)
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post)
    {
        $this->post = $post;
        return $this;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo(string $tipo)
    {
        if (!in_array($tipo, ['me_gusta', 'no_me_gusta'])) {
            throw new \InvalidArgumentException("Tipo de reacción no válido");
        }
        $this->tipo = $tipo;
        return $this;
    }
}