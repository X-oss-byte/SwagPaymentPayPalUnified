#!/usr/bin/env sh
set -eu

# TODO: Move this into the Makefile

DIR="${0%/*}"
DISPLAY="${DISPLAY:-}"

CI_PROJECT_DIR="${CI_PROJECT_DIR:-}"
CI_JOB_STAGE="${CI_JOB_STAGE:-}"
CI_JOB_NAME="${CI_JOB_NAME:-}"

if [ -z "${CI_PROJECT_DIR}" ]; then
    export CI_PROJECT_DIR="$(pwd)"
fi

mkdir -m 777 -p ${CI_PROJECT_DIR}/build/logs

if [ -z "${CI_JOB_STAGE}" ] && [ -n "${1}" ]; then
    export CI_JOB_STAGE="${1}"
    shift 1
fi

if [ -z "${CI_JOB_NAME}" ] && [ -n "${1}" ]; then
    export CI_JOB_NAME="${1}"
    shift 1
fi

if [ -n "${CI:-}" ]; then
    if [ -e "${DIR}/compose.${CI_JOB_STAGE}.${CI_JOB_NAME}.yml" ]; then
        docker compose -f "${DIR}/compose.${CI_JOB_STAGE}.yml" -f "${DIR}/compose.${CI_JOB_STAGE}.${CI_JOB_NAME}.yml" --profile "${CI_JOB_NAME}" $@
    else
        docker compose -f "${DIR}/compose.${CI_JOB_STAGE}.yml" --profile "${CI_JOB_NAME}" $@
    fi
else
    make --silent -C "${DIR}" .env

    if [ -e "${DIR}/compose.${CI_JOB_STAGE}.${CI_JOB_NAME}.yml" ]; then
        docker compose -f "${DIR}/compose.${CI_JOB_STAGE}.yml" -f "${DIR}/compose.${CI_JOB_STAGE}.${CI_JOB_NAME}.yml" --profile "${CI_JOB_NAME}" --env-file "${DIR}/.env" $@
    else
        docker compose -f "${DIR}/compose.${CI_JOB_STAGE}.yml" --profile "${CI_JOB_NAME}" --env-file "${DIR}/.env" $@
    fi
fi
