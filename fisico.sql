-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema mydb
-- -----------------------------------------------------
-- -----------------------------------------------------
-- Schema perfil_de_usuario
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema perfil_de_usuario
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `perfil_de_usuario` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `perfil_de_usuario` ;

-- -----------------------------------------------------
-- Table `perfil_de_usuario`.`usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfil_de_usuario`.`usuario` (
  `id_usuario` INT NOT NULL AUTO_INCREMENT,
  `nome_completo` VARCHAR(45) NOT NULL,
  `email` VARCHAR(45) NOT NULL,
  `telefone` VARCHAR(11) NOT NULL,
  `senha_hash` VARCHAR(255) NOT NULL,
  `avatar_url` MEDIUMBLOB NULL DEFAULT NULL,
  `criado_em` DATETIME NOT NULL,
  `atualizado_em` DATETIME NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `perfil_de_usuario`.`log_auditoria`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfil_de_usuario`.`log_auditoria` (
  `id_usuario` INT NOT NULL,
  `acao_realizada` VARCHAR(120) NOT NULL,
  `endereco_ip` VARCHAR(38) NOT NULL,
  `data_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  INDEX `fk_id_usuario_LA_idx` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `fk_id_usuario_LA`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `perfil_de_usuario`.`usuario` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `perfil_de_usuario`.`preferencia_usuario`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `perfil_de_usuario`.`preferencia_usuario` (
  `id_usuario` INT NOT NULL,
  `alertas_sistema` TINYINT NOT NULL DEFAULT '0',
  `emails_seguranca` TINYINT NOT NULL DEFAULT '1',
  `emails_marketing` TINYINT NOT NULL DEFAULT '0',
  `pesquisa_opiniao` TINYINT NOT NULL DEFAULT '0',
  `atualizado_em` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_usuario`),
  INDEX `fk_id_usuario_PU_idx` (`id_usuario` ASC) VISIBLE,
  CONSTRAINT `fk_id_usuario_PU`
    FOREIGN KEY (`id_usuario`)
    REFERENCES `perfil_de_usuario`.`usuario` (`id_usuario`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
