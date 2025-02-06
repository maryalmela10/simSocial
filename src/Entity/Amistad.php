<?php

namespace App\Entity;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;

#[ORM\Entity]
#[ORM\Table(name: "amistades")]
class Amistad
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type:'integer', name:'id')]
    private int $id;

    #[ORM\Column(type:'integer', name:'usuario_a_id')]
    private int $usuario_a_id;

    #[ORM\Column(type:'integer', name:'usuario_b_id')]
    private int $usuario_b_id;

    #[ORM\Column(type: 'string', name:'estado')]
    private $estado; // 'pendiente', 'aceptado', 'rechazado'

    #[ORM\Column(type: 'datetime', name:'fecha_solicitud')]
    private $fecha_solicitud;

    // Getters and setters

    public function getId()
    {
        return $this->id;
    }
    
    public function getUsuario_a_id()
    {
        return $this->usuario_a_id;
    }

    public function setUsuario_a_id($usuario_a_id)
    {
        $this->usuario_a_id = $usuario_a_id;
    }

    public function getUsuario_b_id()
    {
        return $this->usuario_b_id;
    }

    public function setUsuario_b_id($usuario_b_id)
    {
        $this->usuario_b_id = $usuario_b_id;
    }

    public function getEstado()
    {
        return $this->estado;
    }

    public function setEstado($estado)
    {
        $this->estado = $estado;
    }

    
    public function getFecha_solicitud()
    {
        return $this->fecha_solicitud;
    }

    public function setFecha_solicitud($fecha_solicitud)
    {
        $this->fecha_solicitud = $fecha_solicitud;
    }

}