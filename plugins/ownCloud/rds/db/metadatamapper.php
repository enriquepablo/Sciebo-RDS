<?php

namespace OCA\RDS\Db;

use \OCA\RDS\Db\Metadata;
use \OCA\RDS\Service\NotFoundException;

class MetadataMapper
{
    private $rdsURL = 'https://sciebords-dev.uni-muenster.de/metadata';

    public function __construct()
    {
    }

    public function update($metadata)
    {
        $curl = curl_init($this->rdsURL . '/user/' . $metadata->getUserId() . '/research/' . $metadata->getResearchIndex());
        $options = [CURLOPT_RETURNTRANSFER => true];
        curl_setopt_array($curl, $options);
        curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($metadata->getMetadata()));
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, 'PATCH');
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        // Set the content type to application/json
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type:application/json'));

        $response = json_decode(curl_exec($curl));
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode >= 300) {
            return NULL;
        }

        return true;
    }

    public function find($userId, $researchIndex, $port)
    {
        $metadatas = $this->findAll($userId, $researchIndex);

        foreach ($metadatas as $md) {
            if ($md->port == $port) {
                return $md;
            }
        }

        throw new NotFoundException('No metadata for ' . $port . ' found.');
    }

    public function jsonschema()
    {
        return json_encode([
            "kernelversion" => "custom",
            "schema" => '{
                "additionalProperties": false,
                "description": "Describe information needed for deposit module.",
                "id": "http://zenodo.org/schemas/deposits/records/legacyjson.json",
                "properties": {
                  "$schema": {
                      "type": "string"
                  },
                  "upload_type": {
                      "additionalProperties": false,
                      "default": "publication",
                      "description": "Record upload type.",
                      "enum": [
                          "publication",
                          "poster",
                          "presentation",
                          "dataset",
                          "image",
                          "video",
                          "software",
                          "lesson",
                          "other"
                        ],  
                      "type": "string"
                  },
                  "publication_type": {
                      "additionalProperties": false,
                      "description": "Publication type.",
                      "default": "other",
                      "enum": [
                          "book",
                          "section",
                          "conferencepaper",
                          "article",
                          "patent",
                          "preprint",
                          "report",
                          "deliverable",
                          "milestone",
                          "proposal",
                          "softwaredocumentation",
                          "thesis",
                          "technicalnote",
                          "workingpaper",
                          "datamanagementplan",
                          "annotationcollection",
                          "taxonomictreatment",
                          "other"
                        ],
                      "type": "string"
                  },
                  "image_type": {
                      "additionalProperties": false,
                      "default": "other",
                      "description": "Image type.",
                      "enum": [
                          "figure",
                          "plot",
                          "drawing",
                          "diagram",
                          "photo",
                          "other"
                        ],
                        "type": "string"
                  },
                  "publication_date": {
                      "description": "Record publication date (IS8601-formatted). EDTF support to be added for field.",
                      "type": "string"
                  },
                  "title": {
                      "description": "Record title.",
                      "type": "string"
                  },
                  "creators": {
                      "description": "Creators of record in order of importance.",
                      "items": {
                        "additionalProperties": false,
                        "properties": {
                          "affiliation": {
                            "description": "Affiliation for the purpose of this specific record.",
                            "type": "string"
                          },
                          "gnd": {
                            "description": "Gemeinsame Normdatei identifier for creator.",
                            "type": "string"
                          },
                          "name": {
                            "description": "Full name of person or organisation. Personal name format: family, given.",
                            "type": "string"
                          },
                          "orcid": {
                            "description": "ORCID identifier for creator.",
                            "type": "string"
                          }
                        },
                        "type": "object"
                      },
                      "type": "array"
                  },
                  "description": {
                      "description": "Description/abstract for record.",
                      "type": "string"
                  },
                  "access_right": {
                      "default": "open",
                      "description": "Access right for record.",
                      "enum": [
                        "open",
                        "embargoed",
                        "restricted",
                        "closed"
                      ],
                      "type": "string"
                  },
                  "license": {
                      "description": "License for embargoed/open access content.",
                      "title": "License",
                      "type": "string",
                      "default": "CC-BY-4.0"
                  },
                  "embargo_date": {
                      "description": "Embargo date of record (ISO8601 formatted date).",
                      "title": "Embargo Date",
                      "type": "string"
                  },
                  "access_conditions": {
                    "description": "Conditions under which access is given if record is restricted.",
                    "title": "Access conditions",
                    "type": "string"
                  },  
                  "communities": {
                    "description": "List of community identifiers.",
                    "items": {
                      "additionalProperties": false,
                      "properties": {
                        "identifier": {
                          "type": "string"
                        }
                      },
                      "type": "object"
                    },
                    "type": "array"
                  },
                  "contributors": {
                    "description": "Contributors in order of importance.",
                    "items": {
                      "additionalProperties": false,
                      "properties": {
                        "affiliation": {
                          "description": "Affiliation for the purpose of this specific record.",
                          "type": "string"
                        },
                        "gnd": {
                          "description": "Gemeinsame Normdatei identifier for creator.",
                          "type": "string"
                        },
                        "name": {
                          "description": "Full name of person or organisation. Personal name format: family, given.",
                          "type": "string"
                        },
                        "orcid": {
                          "description": "ORCID identifier for creator.",
                          "type": "string"
                        },
                        "type": {
                          "enum": [
                            "ContactPerson",
                            "DataCollector",
                            "DataCurator",
                            "DataManager",
                            "Distributor",
                            "Editor",
                            "HostingInstitution",
                            "Other",
                            "Producer",
                            "ProjectLeader",
                            "ProjectManager",
                            "ProjectMember",
                            "RegistrationAgency",
                            "RegistrationAuthority",
                            "RelatedPerson",
                            "ResearchGroup",
                            "RightsHolder",
                            "Researcher",
                            "Sponsor",
                            "Supervisor",
                            "WorkPackageLeader"
                          ],
                          "type": "string"
                        }
                      },
                      "type": "object"
                    },
                    "title": "Contributors",
                    "type": "array"
                  },
                  "doi": {
                    "description": "Digital Object Identifier (DOI).",
                    "type": "string"
                  },
                  "grants": {
                    "description": "Source of grants/awards which have funded all or part of this particular record.",
                    "items": {
                      "additionalProperties": false,
                      "properties": {
                        "id": {
                          "type": "string"
                        }
                      },
                      "type": "object"
                    },
                    "type": "array"
                  },
                  "imprint_isbn": {
                    "type": "string"
                  },
                  "imprint_place": {
                    "type": "string"
                  },
                  "imprint_publisher": {
                    "type": "string"
                  },
                  "journal_issue": {
                    "description": "Journal issue.",
                    "title": "Journal issue",
                    "type": "string"
                  },
                  "journal_pages": {
                    "description": "Journal page(s).",
                    "title": "Journal page(s)",
                    "type": "string"
                  },
                  "journal_title": {
                    "description": "Journal title.",
                    "title": "Journal title",
                    "type": "string"
                  },
                  "journal_volume": {
                    "description": "Journal volume.",
                    "title": "Journal volume",
                    "type": "string"
                  },
                  "keywords": {
                    "description": "Free text keywords.",
                    "items": {
                      "title": "Keyword",
                      "type": "string"
                    },
                    "title": "Keywords",
                    "type": "array"
                  },
                  "conference_acronym": {
                    "title": "Acronym",
                    "type": "string"
                  },
                  "conference_dates": {
                    "title": "Dates",
                    "type": "string"
                  },
                  "conference_place": {
                    "title": "Place",
                    "type": "string"
                  },
                  "conference_session": {
                    "title": "Session",
                    "type": "string"
                  },
                  "conference_session_part": {
                    "title": "Part of session",
                    "type": "string"
                  },
                  "conference_title": {
                    "title": "Conference Title",
                    "type": "string"
                  },
                  "conference_url": {
                    "title": "URL",
                    "type": "string"
                  },
                  "notes": {
                    "description": "Additional notes for record.",
                    "title": "Additional notes",
                    "type": "string"
                  },
                  "partof_pages": {
                    "title": "Pages",
                    "type": "string"
                  },
                  "partof_title": {
                    "title": "Book Title",
                    "type": "string"
                  },
                  "references": {
                    "description": "Raw textual references when identifier is not known.",
                    "items": {
                      "type": "string"
                    },
                    "type": "array"
                  },
                  "related_identifiers": {
                    "description": "Related research outputs.",
                    "items": {
                      "properties": {
                        "identifier": {
                          "description": "Identifier of research output.",
                          "type": "string"
                        },
                        "relation": {
                          "description": "Relation type.",
                          "enum": [
                            "isCitedBy",
                            "cites",
                            "isSupplementTo",
                            "isSupplementedBy",
                            "isContinuedBy",
                            "continues",
                            "hasMetadata",
                            "isMetadataFor",
                            "isNewVersionOf",
                            "isPreviousVersionOf",
                            "isPartOf",
                            "hasPart",
                            "isReferencedBy",
                            "references",
                            "isDocumentedBy",
                            "documents",
                            "isCompiledBy",
                            "compiles",
                            "isVariantFormOf",
                            "isOrignialFormOf",
                            "isIdenticalTo",
                            "isAlternateIdentifier",
                            "isReviewedBy",
                            "reviews",
                            "isDerivedFrom",
                            "isSourceOf"
                          ],
                          "type": "string"
                        },
                        "scheme": {
                          "description": "Persistent identifier scheme.",
                          "enum": [
                            "ads",
                            "ark",
                            "arxiv",
                            "bioproject",
                            "biosample",
                            "doi",
                            "ean13",
                            "ean8",
                            "ensembl",
                            "genome",
                            "gnd",
                            "hal",
                            "handle",
                            "isbn",
                            "isni",
                            "issn",
                            "istc",
                            "lsid",
                            "orcid",
                            "pmcid",
                            "pmid",
                            "purl",
                            "refseq",
                            "sra",
                            "uniprot",
                            "url",
                            "urn",
                            "swh",
                            "ascl"
                          ],
                          "type": "string"
                        },
                        "resource_type": {
                          "type": "string"
                        }
                      },
                      "type": "object"
                    },
                    "type": "array"
                  },
                  "openaire_type": {
                    "type": "string",
                    "descritpion": "OpenAIRE type."
                  },
                  "subjects": {
                    "description": "Subjects from specific vocabularies.",
                    "items": {
                      "additionalProperties": false,
                      "properties": {
                        "identifier": {
                          "description": "Subjects term identifier.",
                          "type": "string"
                        },
                        "scheme": {
                          "description": "Identifier scheme.",
                          "type": "string"
                        },
                        "term": {
                          "description": "Subject term value.",
                          "type": "string"
                        }
                      },
                      "type": "object"
                    },
                    "type": "array"
                  },
                  "thesis_supervisors": {
                    "description": "Supervisors of thesis in order of importance.",
                    "items": {
                      "additionalProperties": false,
                      "properties": {
                        "affiliation": {
                          "description": "Affiliation for the purpose of this specific record.",
                          "type": "string"
                        },
                        "gnd": {
                          "description": "Gemeinsame Normdatei identifier for creator.",
                          "type": "string"
                        },
                        "name": {
                          "description": "Full name of person or organisation. Personal name format: family, given.",
                          "type": "string"
                        },
                        "orcid": {
                          "description": "ORCID identifier for creator.",
                          "type": "string"
                        }
                      },
                      "type": "object"
                    },
                    "type": "array"
                  },
                  "thesis_university": {
                    "description": "Awarding university in case of a thesis.",
                    "type": "string"
                  }
                },
                "required": ["upload_type","publication_date","title","creators","description","access_right"],
                "title": "Zenodo Legacy Deposit Schema v1.0.0",
                "type": "object"
              }'
        ]);

        $url = $this->rdsURL . '/jsonschema';

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curl);
        $response = json_decode($result, true);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode >= 300) {
            return NULL;
        }

        return $result;
    }

    public function findAll($userId, $researchIndex)
    {
        $url = $this->rdsURL . '/user/' . $userId . '/research/' . $researchIndex;

        $curl = curl_init();
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        $result = curl_exec($curl);
        $response = json_decode($result, true);
        $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);

        if ($httpcode >= 300) {
            return NULL;
        }

        $result = [];

        foreach ($response['list'] as $rdsMetadata) {
            $metadata = new Metadata();

            $metadata->setUserId($userId);
            $metadata->setResearchIndex($researchIndex);
            $metadata->setPort($rdsMetadata['port']);
            $metadata->setMetadata($rdsMetadata['metadata']);

            $result[] = $metadata;
        }

        return $result;
    }
}
