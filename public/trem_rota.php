<?php

function bloquear_rota_ativa(mysqli $conexao, int $idRota): void
{
    $stmt = $conexao->prepare("SELECT ativa FROM rota WHERE id_rota = ? FOR UPDATE");
    $stmt->bind_param("i", $idRota);
    $stmt->execute();
    $rota = $stmt->get_result()->fetch_assoc();
    $stmt->close();
    if (!$rota || (int)$rota["ativa"] !== 1) {
        throw new DomainException("Selecione uma rota ativa.");
    }
}

function vincular_trem_rota(mysqli $conexao, int $idTrem, int $idRota): void
{
    $stmt = $conexao->prepare("INSERT INTO trem_rota (id_trem, id_rota) VALUES (?, ?)");
    $stmt->bind_param("ii", $idTrem, $idRota);
    try {
        $stmt->execute();
    } catch (mysqli_sql_exception $e) {
        if ((int)$e->getCode() === 1062) {
            throw new DomainException("O trem já está em uma rota. Encerre a rota atual para liberá-lo.", 0, $e);
        }
        throw $e;
    } finally {
        $stmt->close();
    }
}
