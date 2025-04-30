<?php
    // namespace App\Entities;

    //use Doctrine\ORM\Mapping as ORM;

    /**
     * @ORM\Entity
     * @ORM\Table(name="users")
     */
    class UserEntity {
        /**
         * @ORM\IdUsuario
         * @ORM\GeneratedValue
         * @ORM\Column(type="integer")
         */
        private $idUsuario;

        /**
         * @ORM\Column(type="string", length=255, unique=true)
         */
        private $email;

        /**
         * @ORM\Column(type="string", length=255)
         */
        private $nombre;

        public function __construct(?int $idUsuario = null, string $email = '', string $nombre = '') {
            $this->idUsuario = $idUsuario;
            $this->email = $email;
            $this->nombre = $nombre;
        }

        public function getIdUsuario(): ?int {
            return $this->idUsuario;
        }

        public function getEmail(): ?string {
            return $this->email;
        }

        public function setEmail(string $email): self {
            $this->email = $email;
            return $this;
        }

        public function getNombre(): ?string {
            return $this->nombre;
        }

        public function setNombre(string $nombre): self {
            $this->nombre = $nombre;
            return $this;
        }

    }
?>