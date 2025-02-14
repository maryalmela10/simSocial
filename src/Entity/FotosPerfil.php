<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Entity\Usuario;

#[ORM\Entity]
#[ORM\Table(name: "fotos_perfil")]
class FotosPerfil
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: "integer")]
    private ?int $id = null;

    #[ORM\OneToOne(targetEntity: Usuario::class, inversedBy: "fotoPerfil")]
    #[ORM\JoinColumn(name: "usuario_id", referencedColumnName: "id", onDelete: "CASCADE")]
    private ?Usuario $usuario = null;

    #[ORM\Column(type: "string", length: 255)]
    private ?string $urlImagen = null;

    #[ORM\Column(type: "datetime", options: ["default" => "CURRENT_TIMESTAMP"])]
    private \DateTime $fechaSubida;

    public function __construct()
    {
        $this->fechaSubida = new \DateTime();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getUrlImagen(): ?string
    {
        return $this->urlImagen;
    }

    public function setUrlImagen(?string $urlImagen): self
    {
        $this->urlImagen = $urlImagen;
        return $this;
    }

    public function getFechaSubida(): \DateTime
    {
        return $this->fechaSubida;
    }
}
