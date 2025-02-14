<?php
namespace App\Entity;
use App\Entity\Usuario;
use Doctrine\ORM\Mapping as ORM;


#[ORM\Entity]
#[ORM\Table(name: "foto_post")]
class FotoPost
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private int $id;

    
    #[ORM\OneToOne(inversedBy: 'fotoPost', targetEntity: Post::class)]
    #[ORM\JoinColumn(name: 'post_id', referencedColumnName: 'id', nullable: false)]
    private ?Post $post = null;

    #[ORM\Column(name: "urlImagen", type: "string", length: 255)]
    private string $urlImagen;

    public function getId(): int
    {
        return $this->id;
    }

    public function getPost(): Post
    {
        return $this->post;
    }

    public function setPost(Post $post): self
    {
        $this->post = $post;
        return $this;
    }

    public function getUrlImagen(): string
    {
        return $this->urlImagen;
    }

    public function setUrlImagen(string $urlImagen): self
    {
        $this->urlImagen = $urlImagen;
        return $this;
    }
}
