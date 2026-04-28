# Layout (relative to this Makefile):
#   diagrams/d2_code/        D2 sources (.d2)
#   diagrams/d2_compiled/    rendered PNGs (build artefact)
#   latex/code/              rapport.tex + any other .tex/.sty
#   latex/compiled/          current rapport_lms.pdf
#   oldV/                    archived previous PDFs (rapport_lms_vN.pdf)
#
# Usage (MODE is mandatory):
#
#   make MODE=new        Treat the build as a NEW VERSION. The current
#                        rapport_lms.pdf (if any) is moved to oldV/ with
#                        a bumped version number BEFORE recompiling.
#
#   make MODE=fix        Treat the build as a small correction / re-run.
#                        The current rapport_lms.pdf is overwritten in
#                        place — nothing is archived.
#
# Other targets:
#   make diagrams        Render all .d2 -> .png (no LaTeX build).
#   make clean           Remove rendered diagrams + LaTeX aux files.
#   make distclean       Like clean, but also wipes latex/compiled/
#                        and oldV/. Destructive.

# tools
D2        ?= d2
PDFLATEX  ?= pdflatex
# D2 render flags tuned for inclusion in an A4 LaTeX document.
D2_FLAGS  ?= --layout=elk --theme=0 --pad=20 --scale=1.4

# paths 
D2_SRC_DIR     := diagrams/d2_code
D2_OUT_DIR     := diagrams/d2_compiled
TEX_SRC_DIR    := latex/code
TEX_OUT_DIR    := latex/compiled
OLDV_DIR       := oldV

REPORT_NAME    := rapport_lms
TEX_FILE       := $(TEX_SRC_DIR)/rapport.tex
PDF_OUT        := $(TEX_OUT_DIR)/$(REPORT_NAME).pdf

# diagram discovery
D2_SOURCES := $(wildcard $(D2_SRC_DIR)/*.d2)
D2_PNGS    := $(patsubst $(D2_SRC_DIR)/%.d2,$(D2_OUT_DIR)/%.png,$(D2_SOURCES))

# default target: refuses to run without MODE
.PHONY: all
all:
ifndef MODE
	@echo ""
	@echo "  ERROR: MODE is required."
	@echo ""
	@echo "  Use one of:"
	@echo "    make MODE=new   -> archive current PDF, then build fresh"
	@echo "    make MODE=fix   -> overwrite current PDF in place"
	@echo ""
	@exit 2
else ifeq ($(filter $(MODE),new fix),)
	@echo ""
	@echo "  ERROR: MODE='$(MODE)' is not a valid mode."
	@echo "  Valid modes: 'new' or 'fix'."
	@echo ""
	@exit 2
else
	@$(MAKE) --no-print-directory build MODE=$(MODE)
endif

# build pipeline
.PHONY: build
build: ensure-dirs $(D2_PNGS) archive-if-needed compile-tex
	@echo ""
	@echo "  Done. Output: $(PDF_OUT)"
	@if [ "$(MODE)" = "new" ]; then \
	  echo "  Mode: NEW VERSION. Previous PDF archived under $(OLDV_DIR)/ (if there was one)."; \
	else \
	  echo "  Mode: FIX. Previous PDF overwritten in place."; \
	fi
	@echo ""

# ensure required directories exist
.PHONY: ensure-dirs
ensure-dirs:
	@mkdir -p $(D2_OUT_DIR) $(TEX_OUT_DIR) $(OLDV_DIR)

# render D2 -> PNG (incremental)
$(D2_OUT_DIR)/%.png: $(D2_SRC_DIR)/%.d2 | ensure-dirs
	@echo "  d2  $< -> $@"
	@$(D2) $(D2_FLAGS) $< $@

.PHONY: diagrams
diagrams: ensure-dirs $(D2_PNGS)

# archive current PDF (only on MODE=new)
# Versions are integers starting at 1. The bump finds the highest existing
# rapport_lms_vN.pdf in oldV/ and adds 1.
.PHONY: archive-if-needed
archive-if-needed:
ifeq ($(MODE),new)
	@if [ -f "$(PDF_OUT)" ]; then \
	  next=$$(ls $(OLDV_DIR) 2>/dev/null \
	    | sed -n 's/^$(REPORT_NAME)_v\([0-9][0-9]*\)\.pdf$$/\1/p' \
	    | sort -n | tail -1); \
	  if [ -z "$$next" ]; then next=1; else next=$$((next + 1)); fi; \
	  archived="$(OLDV_DIR)/$(REPORT_NAME)_v$${next}.pdf"; \
	  mv "$(PDF_OUT)" "$$archived"; \
	  echo "  archived previous PDF -> $$archived"; \
	else \
	  echo "  no previous PDF to archive (first build)"; \
	fi
endif

# compile LaTeX
# Two passes for TOC + page refs. -output-directory keeps aux noise out
# of the source tree. We then move the PDF to latex/compiled/ under the
# canonical name regardless of the .tex filename.
.PHONY: compile-tex
compile-tex: $(D2_PNGS) ensure-dirs
	@echo "  pdflatex pass 1/2..."
	@$(PDFLATEX) -interaction=nonstopmode -halt-on-error \
	    -output-directory=$(TEX_OUT_DIR) $(TEX_FILE) > /dev/null
	@echo "  pdflatex pass 2/2..."
	@$(PDFLATEX) -interaction=nonstopmode -halt-on-error \
	    -output-directory=$(TEX_OUT_DIR) $(TEX_FILE) > /dev/null
	@base=$$(basename $(TEX_FILE) .tex); \
	  if [ "$$base.pdf" != "$(REPORT_NAME).pdf" ]; then \
	    mv "$(TEX_OUT_DIR)/$$base.pdf" "$(PDF_OUT)"; \
	  fi
	@# tidy aux files but keep the PDF
	@rm -f $(TEX_OUT_DIR)/*.aux $(TEX_OUT_DIR)/*.log \
	       $(TEX_OUT_DIR)/*.toc $(TEX_OUT_DIR)/*.out

# cleanup
.PHONY: clean
clean:
	@rm -rf $(D2_OUT_DIR)
	@rm -f $(TEX_OUT_DIR)/*.aux $(TEX_OUT_DIR)/*.log \
	       $(TEX_OUT_DIR)/*.toc $(TEX_OUT_DIR)/*.out
	@echo "  cleaned: rendered diagrams + LaTeX aux files."
	@echo "  (kept: $(PDF_OUT) and $(OLDV_DIR)/*.pdf)"

.PHONY: distclean
distclean:
	@rm -rf $(D2_OUT_DIR) $(TEX_OUT_DIR) $(OLDV_DIR)
	@echo "  distclean: removed diagrams build, latex/compiled/, oldV/."

.PHONY: help
help:
	@sed -n '1,/^# ===/p; /^# Usage/,/^# ===/p' $(firstword $(MAKEFILE_LIST))
